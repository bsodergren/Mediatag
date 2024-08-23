<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Traits\Callables;
use Mediatag\Utilities\MediaArray;

class Genre extends TagBuilder
{
    use Callables;

    public static $genreArray;

    public function __construct()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

    }

    public static function writeTagList($text, $file = false)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (null === parent::$dbConn) {
            parent::$dbConn = new TagDB();
        }
        self::$genreArray = parent::$dbConn->listGenre();
        $textArray        = explode(',', $text);
        foreach ($textArray as $genre) {
            $key = parent::$dbConn->makeKey($genre);
            if (null === MediaArray::search(self::$genreArray, $key)) {
                parent::$dbConn->addGenre($genre);
            }
        }
    }

    public static function clean($text, $file = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        return parent::clean($text, 'Genre');
    }
}
