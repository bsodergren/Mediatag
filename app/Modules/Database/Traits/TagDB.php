<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Traits;

use UTM\Bundle\mysql\MysqliDb;

trait TagDB
{
    // public $dbConn;

    // public function __construct()
    // {
    //     // utminfo(func_get_args());

    //     $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
    // }

    public function __call($name, $arguments)
    {
        $tag = strtolower(\str_replace('get', '', $name));

        return $this->getTag($tag, $arguments);
    }

    // public function getGenre($arguments)
    // {
    //     // utminfo(func_get_args());

    //     $tag = 'genre';

    //     return (new DbMap)->getTag($tag, $arguments);
    // }

    // public function getKeyword($arguments)
    // {
    //     // utminfo(func_get_args());

    //     $tag = 'keyword';

    //     return (new DbMap)->getTag($tag, $arguments);
    // }
}
