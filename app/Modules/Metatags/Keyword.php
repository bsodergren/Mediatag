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

class Keyword extends TagBuilder
{
    use Callables;

    public static $keywordArray;

    public function __construct($videoData)
    {
        utminfo();

        // // UTMlog::Logger(__CLASS__, $this->videoData);
    }

    public function getTagValue() {}

    public static function writeTagList($text, $file = false)
    {
        utminfo();

        if (null === parent::$dbConn) {
            parent::$dbConn = new TagDB();
        }

        self::$keywordArray = parent::$dbConn->listKeyword();
        $textArray          = explode(',', $text);
        foreach ($textArray as $keyword) {
            $keyword = parent::$dbConn->makeKey($keyword);

            if (null === MediaArray::search(self::$keywordArray, $keyword)) {
                parent::$dbConn->addKeyword($keyword);
            }
        }
    }

    public static function clean($text, $file = null)
    {
        utminfo();

        return parent::clean($text, 'keyword');
    }
}
