<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

use Mediatag\Core\Mediatag;

trait StudioMap
{
    public function getStudioPath($text)
    {
        // utminfo(func_get_args());
        utmdump($text);
        $query  = 'SELECT library, path,studio FROM ' . __MYSQL_STUDIOS__ . " WHERE name LIKE '" . $text . "'";
        $result = $this->dbConn->rawQueryOne($query);
utmdump($result);
        if (null !== $result) {

            if ($result['path'] === null ||
                $result['path'] == "") {
                unset($result['path']);
            }
            $path = implode('/', $result);

            return rtrim($path, '/');
        }
        $this->addStudioMap(__LIBRARY__,$text,$text,null);
        $this->getStudioPath($text);
        return false;
    }

    public function addStudioMap($library, $name, $studio, $path) // $library,$name, $path, $studio)
    {
        // utminfo(func_get_args());

        $library = "'" . $library . "'";
        $name    = "'" . $name . "'";
        $studio  = "'" . $studio . "'";

        if (null !== $path) {
            $path = "'" . $path . "'";
        } else {
            $path = 'NULL';
        }

        $query   = 'INSERT IGNORE INTO ' . __MYSQL_STUDIOS__ . '  (library, name, studio, path) VALUES (' . $library . ',' . $name . ',' . $studio . ', ' . $path . ') ';
        $query   = $query . ' ON DUPLICATE KEY UPDATE library=' . $library . ',studio=' . $studio . ',path=' . $path;

        $this->dbConn->rawQuery($query);
    }

    public function dropStudio($library, $name) // $library,$name, $path, $studio)
    {
        // utminfo(func_get_args());

        $library = "'" . $library . "'";
        $name    = "'" . $name . "'";
        $query   = 'DELETE FROM ' . __MYSQL_STUDIOS__ . ' WHERE Library = ' . $library . ' and name = ' . $name . '';
        $this->dbConn->rawQuery($query);
    }
}
