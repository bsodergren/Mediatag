<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

trait StudioMap
{
    public function getStudioPath($text)
    {
        // utminfo(func_get_args());
        $query  = 'SELECT library, path,studio FROM ' . __MYSQL_STUDIOS__ . " WHERE name LIKE '" . $text . "'";
        $result = $this->dbConn->rawQueryOne($query);
        if ($result !== null) {
            if ($result['library'] == 'Pornhub') {
                $result['library'] = 'New';
            }
            //            unset($result['library']);
            if ($result['path'] === null
                || $result['path'] == '') {
                //                  return false;
                unset($result['path']);
            } else {
                unset($result['studio']);
            }
            $path = implode('/', $result);

            return rtrim($path, '/');

            // return rtrim($result['path'], '/');
        }
        $this->addStudioMap(__LIBRARY__, $text, $text, null);
        $this->getStudioPath($text);

        return false;
    }

    public function addStudioMap($library, $name, $studio, $path) // $library,$name, $path, $studio)
    {
        // utminfo(func_get_args());

        $library = "'" . $library . "'";
        $name    = "'" . $name . "'";
        $studio  = "'" . $studio . "'";

        if ($path !== null) {
            $path = "'" . $path . "'";
        } else {
            $path = 'NULL';
        }

        $query = 'INSERT IGNORE INTO ' . __MYSQL_STUDIOS__ . '  (library, name, studio, path) VALUES (' . $library . ',' . $name . ',' . $studio . ', ' . $path . ') ';
        $query = $query . ' ON DUPLICATE KEY UPDATE library=' . $library . ',studio=' . $studio . ',path=' . $path;

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
