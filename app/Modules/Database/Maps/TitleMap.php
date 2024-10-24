<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

use Mediatag\Core\Mediatag;

trait TitleMap
{
    public function addTitle($title)
    {
        utminfo(func_get_args());

        $query = 'INSERT IGNORE INTO ' . __MYSQL_TITLE__ . ' (title) VALUES ("' . $title . '")';
        $this->dbConn->rawQuery($query);
    }

    public function dropTitle($title)
    {
        utminfo(func_get_args());

        $query = 'DELETE FROM ' . __MYSQL_TITLE__ . ' WHERE title = "' . $title . '"';
        $this->dbConn->rawQuery($query);
    }

    public function getTitleMap()
    {
        utminfo(func_get_args());

        $query = 'SELECT title FROM ' . __MYSQL_TITLE__;
        $res   = $this->dbConn->rawQuery($query);
        foreach ($res as $k => $val) {
            $namesArray[] = $val['title'];
        }
        return $namesArray;
    }

}
