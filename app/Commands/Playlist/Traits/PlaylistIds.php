<?php

namespace Mediatag\Commands\Playlist\Traits;

use const DIRECTORY_SEPARATOR;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const PHP_EOL;
// use Nette\Utils\FileSystem as NetteFile;
use const SORT_STRING;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Nette\Utils\Strings;
use UTM\Utilities\Option;

use function array_key_exists;
use function array_slice;
use function count;
use function in_array;
use function is_array;

trait PlaylistIds
{
    public $idList = [];

    public function getUniqueIds($file)
    {
        $idList          = [];
        $archive_content = Filesystem::readLines($file);
        if (is_array($archive_content)) {
            foreach ($archive_content as $lineNum => $line) {
                $idList[] = Strings::after($line, ' ');
            }

            return array_unique($idList);
        }

        return [];
    }

    public function saveUniqueIds($file, $idList)
    {
        $archive_content = Filesystem::readLines($file);
        if (is_array($archive_content)) {
            foreach ($archive_content as $lineNum => $line) {
                $idList[] = Strings::after($line, ' ');
            }
        }

        return array_unique($idList);
    }

    public function getDownloadedIds()
    {
        // utminfo(func_get_args());

        if (Option::isTrue('ignore')) {
            return [];
        }

        $this->idList = $this->getUniqueIds(self::$ARCHIVE);

        $fileidArray = [
            0 => [self::DISABLED, 0],
            1 => [self::MODELHUB, 1],
            2 => [self::IGNORED, 1],
            3 => [self::ERRORIDS, 1],
            // 4 => [self::TRIMMED, 1],
        ];

        foreach ($fileidArray as $i => $fileId) {
            $file = $fileId[0];
            if ($fileId[1] == 0) {
                $idArray = $this->getUniqueIds($file);

                if (is_array($idArray)) {
                    $this->idList = array_merge($this->idList, $idArray);
                }
            }
        }
        $this->getpremiumIds();
        $this->idList = array_merge($this->idList, $this->premiumIds);
        $this->idList = array_merge($this->idList, $this->DownloadableIds);

        return $this->idList;
    }

    public function getpremiumIds()
    {
        // utminfo(func_get_args());

        if (! str_contains('premium', $this->playlist)) {
            $this->premium = str_replace('.txt', '_premium.txt', $this->playlist);
            if (file_exists($this->premium)) {
                $this->premiumIds = Filesystem::readLines($this->premium, [$this, 'getpremiumListIds']);
            }
        }
    }

    public function parseArchive($line)
    {
        $key = Strings::after($line, ' ');
        if (! array_key_exists($key, $this->json_Array)) {
            return $line;
        }

        return false;
    }
}
