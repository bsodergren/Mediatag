<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Utilities\MediaArray;

class Keyword extends TagBuilder
{
    public static $keywordArray;

    public function __construct($videoData)
    {
        // utminfo(func_get_args());

        // // UTMlog::Logger(__CLASS__, $this->videoData);
    }

    public function getTagValue() {}

    public static function writeTagList($text, $file = false)
    {
        // utminfo(func_get_args());

        if (parent::$dbConn === null) {
            parent::$dbConn = new TagDB;
        }

        self::$keywordArray = parent::$dbConn->listKeyword();
        $textArray          = explode(',', $text);
        foreach ($textArray as $keyword) {
            $keyword = parent::$dbConn->makeKey($keyword);

            if (MediaArray::search(self::$keywordArray, $keyword) === null) {
                parent::$dbConn->addKeyword($keyword);
            }
        }
    }

    public static function clean($text, $file = null)
    {
        // utminfo(func_get_args());

        return parent::clean($text, 'keyword');
    }
}
