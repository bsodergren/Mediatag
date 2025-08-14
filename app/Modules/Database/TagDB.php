<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use UTM\Bundle\mysql\MysqliDb;

class TagDB extends Storage
{
    public $dbConn;

    public function __construct()
    {
        // utminfo(func_get_args());

        $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
    }

    public function getGenre($arguments)
    {
        // utminfo(func_get_args());

        $tag = 'genre';

        return (new DbMap())->getTag($tag, $arguments);
    }

    public function getKeyword($arguments)
    {
        // utminfo(func_get_args());

        $tag = 'keyword';

        return (new DbMap())->getTag($tag, $arguments);
    }
}
