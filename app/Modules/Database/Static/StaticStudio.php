<?php

namespace Mediatag\Modules\Database\Static;

use Mediatag\Modules\Database\Storage;

trait StaticStudio
{



public static function init()
{
    $db = new Storage();

}
    public static function getStudioId($studioName)
    {
        self::init();
             $queryTerm = str_replace(' ', '', $studioName);
        $query     = 'SELECT library, path,studio FROM ' . __MYSQL_STUDIOS__ . " WHERE name LIKE '" . $queryTerm . "'";
              utmdd($query);

        $result    = self::$DB->queryOne($query);


    }

    public static function getStudioName($studioId)
    {

    }


}
