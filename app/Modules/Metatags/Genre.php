<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Utilities\MediaArray;

class Genre extends TagBuilder
{
    public static $genreArray;

    public function __construct()
    {
        // utminfo(func_get_args());
    }

    // public static function writeTagList($text, $file = false)
    // {
    //     // utminfo(func_get_args());

    //     if (parent::$dbConn === null) {
    //         Storage::$DB = Storage::$DB;
    //     }

    //     self::$genreArray = Storage::$DB->listGenre();
    //     $textArray        = explode(',', $text);
    //     foreach ($textArray as $genre) {
    //         $key = Storage::$DB->makeKey($genre);
    //         if (MediaArray::search(self::$genreArray, $key) === null) {
    //             Storage::$DB->addGenre($genre);
    //         }
    //     }
    // }

    public static function clean($text, $file = null)
    {
        $tag = 'genre';
        if ($text === '') {
            return null;
        }
        if ($text === false) {
            return null;
        }
        if ($text === 0) {
            return null;
        }
        if ($text === '0') {
            return null;
        }
        if ($text === null) {
            return null;
        }

        $delim = ',';

        $tag_array = explode($delim, $text);
        $arr       = self::fixGenres($tag_array);

        return implode($delim, $arr);
    }

    private static function fixGenres($array)
    {
        $storage = new Storage;
        // $array   = MediaArray::array_iunique($array);
        // $array   = array_filter($array);
        $genreArray = [];
        foreach ($array as $i => $value) {
            if ($value != '') {
                $genreArray[] = self::caseGenre($storage->getTag('genre', $value));
            }
        }
        $genreArray = array_filter($genreArray);
        $string     = implode(',', $genreArray);
        $genreArray = explode(',', $string);
        $genreArray = MediaArray::array_iunique($genreArray);

        return $genreArray;
    }

    private static function caseGenre($text)
    {
        $uppercase = ['mmf', 'mff', 'pov'];
        $text      = trim($text);
        if (str_contains($text, ',')) {
            $pcs = explode(',', $text);
            foreach ($pcs as $str) {
                $arr[] = self::caseGenre($str);
            }

            return implode(',', $arr);
        }

        $found = MediaArray::search($uppercase, strtolower($text), exact: true);
        if ($found) {
            return strtoupper($text);
        }

        return ucwords($text);
    }
}
