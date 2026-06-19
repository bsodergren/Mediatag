<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

trait StudioMap
{
    public function lookupStudio($tag, $text)
    {
        $query  = 'SELECT library, path,studio FROM ' . __MYSQL_STUDIOS__ . " WHERE name LIKE '" . $text . "'";
        $result = $this->queryOne($query);

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
        $this->addStudioToMap($tag, ['library' => 'New', 'name' => $text, 'studio' => $text, 'path' => null]);
        $this->getStudioPathMap($text);

        return false;
    }

    public function addStudioToMap($tag, $array)
    {
        $library = "'" . $array['library'] . "'";
        $name    = "'" . $array['name'] . "'";
        $studio  = "'" . $array['studio'] . "'";

        if ($array['path'] !== null) {
            $path = "'" . $array['path'] . "'";
        } else {
            $path = 'NULL';
        }

        $query = 'INSERT IGNORE INTO ' . __MYSQL_STUDIOS__ . '  (library, name, studio, path) VALUES (' . $library . ',' . $name . ',' . $studio . ', ' . $path . ') ';
        $query = $query . ' ON DUPLICATE KEY UPDATE library=' . $library . ',studio=' . $studio . ',path=' . $path;

        $this->query($query);
    }

    public function dropStudio($library, $name) // $library,$name, $path, $studio)
    {
        // utminfo(func_get_args());

        $library = "'" . $library . "'";
        $name    = "'" . $name . "'";
        $query   = 'DELETE FROM ' . __MYSQL_STUDIOS__ . ' WHERE Library = ' . $library . ' and name = ' . $name . '';
        $this->query($query);
    }
}
