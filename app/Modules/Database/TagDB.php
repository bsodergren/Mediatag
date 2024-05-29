<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use UTM\Bundle\mysql\MysqliDb;
use Mediatag\Modules\Database\DbMap;

class TagDB extends Storage
{
    public $dbConn;

    public function __construct()
    {
        $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
    }

    public function getGenre($arguments)
    {
        $tag = 'genre';

        return (new DbMap)->getTag($tag, $arguments);
    }

    public function getKeyword($arguments)
    {
        $tag = 'keyword';

        return (new DbMap)->getTag($tag, $arguments);
    }

}
