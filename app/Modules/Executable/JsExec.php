<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\MediaCache;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Traits\Callables;
use Nette\Utils\Callback;
use UTM\Utilities\Option;

class JsExec extends MediatagExec
{
    use Callables;

    public $wordList = "";
    private $cacheUpdate = false;

    public $wordMap = __CONFIG_LIB__ . '/config/data/map/Words.txt';
    private $wordCache = "JsDictWordMap";

    public function __construct($videoData = null, $input = null, $output = null)
    {

        $this->getWordMap();

        foreach(ARTIST_MAP as $k => $v) {
            $artists[] = str_replace("_", ",", $v['name']);
        }

        $artistArray = explode(",", implode(",", $artists));
        $array = array_unique($artistArray);
        $this->wordList = $this->wordList . ",". implode(",", $array);

    }

    private function identical_values($arrayA, $arrayB)
    {
        sort($arrayA);
        sort($arrayB);

        return $arrayA == $arrayB;
    }

    public function getWordMap()
    {

        $archive_content = Filesystem::readLines($this->wordMap, function ($line) {return trim($line);});
        $array = MediaCache::get($this->wordCache);
        if (! is_array($array)) {
            MediaCache::put($this->wordCache, $archive_content);
            $array = $archive_content;
            $this->cacheUpdate = true;
        } else {

            if($this->identical_values($archive_content, $array) == false) {
                MediaCache::put($this->wordCache, $archive_content);
                $array = $archive_content;
                $this->cacheUpdate = true;
            }
        }
        if (Option::isTrue('cacheUpdate')) {
            MediaCache::put($this->wordCache, $archive_content);
            $array = $archive_content;
            $this->cacheUpdate = true;
        }

        if (is_array($array)) {
            $this->wordList = implode(",", $array);
        }
    }

    public function getTitle($string)
    {
        return $string;

        // $command = [
        //     'node',
        //         '/home/bjorn/scripts/Mediatag/bin/wordSplit.js',
        //         $string,
        //         $this->wordList,
        //     ];

        // $callback = Callback::check([$this, 'ReadOutput']);

        // $this->exec($command, $callback);

        // return  trim($this->stdout);
    }

    public function read($string)
    {
        $cacheFile = md5($string);
        $title = MediaCache::get($cacheFile);


        if($title === false) {
            $title = $this->getTitle($string);
            MediaCache::put($cacheFile, $title);
        }

        if($this->cacheUpdate === true) {
            $title = $this->getTitle($string);
            MediaCache::put($cacheFile, $title);
        }

        return $title;

    }
}
