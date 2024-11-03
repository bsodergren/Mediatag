<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Traits\Callables;
use Nette\Utils\Callback;

class JsExec extends MediatagExec
{
    use Callables;

    public $wordList     = "";
    private $cacheUpdate = false;
    public $video_key    = null;

    public $wordMap      = __CONFIG_LIB__ . '/data/map/Words.txt';
    private $wordCache   = "JsDictWordMap";

    public function __construct($video_key = null, $input = null, $output = null)
    {
        utminfo(func_get_args());


        $this->getWordMap();
        $this->video_key = "jsCache_" . $video_key;

        foreach (ARTIST_MAP as $k => $v) {
            $artists[] = str_replace("_", ",", $v['name']);
        }

        $artistArray     = explode(",", implode(",", $artists));
        $array           = array_unique($artistArray);
        $this->wordList  = $this->wordList . "," . implode(",", $array);

    }

    private function identical_values($arrayA, $arrayB)
    {
        utminfo(func_get_args());

        sort($arrayA);
        sort($arrayB);

        return $arrayA == $arrayB;
    }

    public function getWordMap()
    {
        utminfo(func_get_args());


        $archive_content = Filesystem::readLines($this->wordMap, function ($line) {return trim($line);});

        $array           = MediaCache::get($this->wordCache);

        if ($array === false) {
            $array = $archive_content;
        } else {
            if ($this->identical_values($archive_content, $array) == false) {
                $array = $archive_content;
            }
        }

        MediaCache::put($this->wordCache, $array);

        if (is_array($array)) {
            $this->wordList = implode(",", $array);
        }
    }

    public function getTitle($string)
    {
        utminfo(func_get_args());

        // return $string;

        $command  = [
            'node',
            '/home/bjorn/scripts/Mediatag/bin/wordSplit.js',
            $string,
            $this->wordList,
        ];

        $callback = Callback::check([$this, 'ReadOutput']);

        $this->exec($command, $callback);

        return trim($this->stdout);
    }

    public function read($string)
    {
        utminfo(func_get_args());

        if ($this->video_key === null) {
            $cacheFile = md5($string);
        } else {
            $cacheFile = $this->video_key;
        }

        $title = MediaCache::get($cacheFile);

        if ($title === false) {
            $title = $this->getTitle($string);
        } else {

            if ($this->identical_values([$title], [$string]) == false) {
                $title = $this->getTitle($string);
            }
        }

        MediaCache::put($cacheFile, $title);

        return $title;

    }
}
