<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;


use UTM\Bundle\mysql\MysqliDb;
use Mediatag\Modules\Database\DbMap;

class TagDB extends Storage
{
    public $dbConn;

    public function __construct()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
    }

    public function getGenre($arguments)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $tag = 'genre';

        return (new DbMap())->getTag($tag, $arguments);
    }

    public function getKeyword($arguments)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $tag = 'keyword';

        return (new DbMap())->getTag($tag, $arguments);
    }

}
