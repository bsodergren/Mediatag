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

    public static function writeTagList($text, $file = false)
    {
        // utminfo(func_get_args());

        if (parent::$dbConn === null) {
           Storage::$DB =Storage::$DB;
        }
        self::$genreArray =Storage::$DB->listGenre();
        $textArray        = explode(',', $text);
        foreach ($textArray as $genre) {
            $key =Storage::$DB->makeKey($genre);
            if (MediaArray::search(self::$genreArray, $key) === null) {
               Storage::$DB->addGenre($genre);
            }
        }
    }

    public static function clean($text, $file = null)
    {
        // utminfo(func_get_args());

        return parent::clean($text, 'genre');
    }
}
