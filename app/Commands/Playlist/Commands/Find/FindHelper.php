<?php

namespace Mediatag\Commands\Playlist\Commands\Find;

use const DIRECTORY_SEPARATOR;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const PHP_EOL;
use const SORT_STRING;

use Mediatag\Commands\Playlist\Traits\PlaylistIds;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Nette\Utils\Strings;
use UTM\Utilities\Option;

use function array_key_exists;

trait FindHelper
{
    use PlaylistIds;

    public function findObjects()
    {
        if (Option::isTrue('json')) {
            Mediatag::$output->writeln('<info> missing json data</info>');
            $this->findJson();
        } elseif (Option::isTrue('missing')) {
            Mediatag::$output->writeln('<info> missing videos from Yt Archive</info>');
            $this->findMissing();
        }
        Mediatag::$output->writeln('<info> What are you trying to find? </info>');

        return 0;
    }

    private function saveMissingPlaylist($content, $playlist_file)
    {
        $missingCnt = count($content);
        Mediatag::$output->writeln('<info> missing ' . $missingCnt . ' files </info>');
        foreach ($content as $v => $key) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
        }
        Filesystem::writeFile($playlist_file, $file_string);
    }

    private function searchVideoList($searchArray)
    {
        foreach ($searchArray as $id) {
            if (! array_key_exists($id, $this->VideoList['file'])) {
                // utmdd($id, $this->VideoList['file'][$id]);
                $missing[] = $id;
            }
        }

        return $missing;
    }

    public function findMissing()
    {
        $playlist_file = 'missing_playlist.txt';
        $ids           = $this->getDownloadedIds();

        $missing = $this->searchVideoList($ids);
        $this->removeFromArchive($missing);

        $this->saveMissingPlaylist($missing, $playlist_file);
    }

    public function findJson()
    {
        // utminfo(func_get_args());
        $ph_keys       = [];
        $file_string   = '';
        Finder::$depth = 1;
        $file_array    = Mediatag::$finder->Search(__JSON_CACHE_DIR__, '*.json', exit: false);

        foreach ($file_array as $key => $val) {
            $ph_key = basename($val, '.info.json');
            if (! str_starts_with($ph_key, 'x')) {
                $ph_keys[] = $ph_key;
            }
        }
        $missing = $this->searchVideoList($ph_keys);
        $this->saveMissingPlaylist($missing, self::JSONPLAYLIST);
    }
    // foreach ($this->VideoList['file'] as $key => $val) {
    //     if (Option::istrue('missing')) {
    //         $this->json_Array[$key] = $val;
    //     } else {
    //         if (array_key_exists($key, $this->json_Array)) {
    //             unset($this->json_Array[$key]);
    //         }
    //     }
    // }
    // // UtmDump($this->json_Array);
    // //        $archive_array = Filesystem::readLines(self::$ARCHIVE, [$this, 'parseArchive']);
    // //        Filesystem::writeFile(self::$ARCHIVE, $archive_array);

    // foreach ($this->json_Array as $key => $v) {
    //     $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
    // }

    // Filesystem::writeFile(self::JSONPLAYLIST, $file_string);

    public function findFiles()
    {
        // utminfo(func_get_args());
        Mediatag::$output->writeln('<info> Looking for  missing files</info>');
        $files = Finder::Find('*.mp4', __PLEX_HOME__ . '/Pornhub', exit: false);

        foreach ($files as $file) {
            $key = File::getVideoKey(basename($file));
            if (str_starts_with($key, 'x')) {
                continue;
            }

            $existing_ids[] = $key;
            $archive_ids[]  = 'pornhub ' . $key;
        }

        Mediatag::$output->writeln('<info> found ' . count($archive_ids) . ' files</info>');
        $this->ids = $existing_ids;

        $archive_content = Filesystem::readLines(self::$ARCHIVE);
        $diff            = array_diff($archive_content, $archive_ids);
        if (is_array($diff)) {
            Mediatag::$output->writeln('<info> found ' . count($diff) . ' missing files</info>');
            if (count($diff) > 0) {
                foreach ($diff as $lineNum => $line) {
                    $idList[] = Strings::after($line, ' ');
                }

                $file_string = '';
                foreach ($idList as $v => $key) {
                    $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
                }

                Filesystem::writeFile(self::MISSING_PLAYLIST, $file_string);
            }
        }

        // utmdd($file_string);
        // $archive_content = array_merge($archive_content, $archive_ids);
        // $archive_array   = array_unique($archive_content);
        $archive_array = array_unique($archive_ids);
        // utmdump(__METHOD__);
        Filesystem::writeFile(self::$ARCHIVE, $archive_array);

        return 0;
        if (isset($this->playlist)) {
            $f      = file($this->playlist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $before = count($f);
            if ($before > 0) {
                $array = Filesystem::readLines($this->playlist, [$this, 'compactPlaylist']);
                $array = array_unique($array);
                $after = count($array);

                Mediatag::$output->writeln(PHP_EOL . 'before, <info>' . $before . '</info> and now after, <info>' . $after . ' </info>');
                $trimmedLines = $before - $after;
                Filesystem::writePlaylist($this->playlist, $array);
                $text = 'trimmed ' . $trimmedLines . ' from the playlist';
                Mediatag::$output->writeln('<info>' . $text . '</info>');
                if ($after == 0) {
                    Mediatag::$output->writeln('<info> All files downloaded</info>');
                    Filesystem::delete($this->playlist);

                    exit;
                }
            }
        }
    }
}
