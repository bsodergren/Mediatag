<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Traits\Callables;
use Nette\Utils\Callback;
use Nette\Utils\Strings;

class Studio extends TagBuilder
{
    use Callables;

    public function __construct($videoData)
    {
        utminfo();

        // // UTMlog::Logger('data', $this->videoData);
    }

    public function getTagValue() {}

    public static function getStudioFile($type, $getpaths = true)
    {
        utminfo();

        if ('A' == $type) {
            $fileDB = Mediatag::$amateurFile;
        } else {
            $fileDB = Mediatag::$channelFile;
        }

        if (true === $getpaths) {
            $callback = 'studioPaths';
        } else {
            $callback = null; // = Callback::check([$self,'studioList']);
        }

        return Filesystem::readLines($fileDB, $callback);
    }

    public static function addStudiotoFile($type, $studio, $newPath = false)
    {
        utminfo();

        $type     = strtoupper($type);

        if ('A' == $type) {
            $fileName = Mediatag::$amateurFile;
            $varName  = 'amateurArray';
            $fileDB   = self::getStudioFile('A', false);
        } else {
            $fileName = Mediatag::$channelFile;
            $varName  = 'channelArray';
            $fileDB   = self::getStudioFile('C', false);
        }

        foreach ($fileDB as $i => $line) {
            $studioDB = $line;
            if (str_contains($line, ':')) {
                $studioDB = Strings::before($line, ':');
            }
            if ($studioDB == $studio) {
                unset($fileDB[$i]);
            }
        }

        if (false !== $newPath) {
            $studio = $studio . ':' . $newPath;
        }

        $fileDB[] = $studio;
        sort($fileDB, \SORT_STRING);

        Filesystem::writeFile($fileName, $fileDB, false);

        // /        MediaFilesystem::writeArray($fileName. ".php", $varName, $fileDB);
    }
}
