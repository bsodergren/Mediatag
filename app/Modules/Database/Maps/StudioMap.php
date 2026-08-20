<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

use UTM\Utilities\Option;

trait StudioMap
{
    public function getStudioPathMap($tag, $text) {}

    public function lookupStudio($tag, $text, $returnPath = false)
    {
        $queryTerm = str_replace(' ', '', $text);
        $query     = 'SELECT library, path,studio FROM ' . __MYSQL_STUDIOS__ . " WHERE name LIKE '" . $queryTerm . "'";
        //        utmdd($query);

        $result    = $this->queryOne($query);
        if (null !== $result) {
            if (true === $returnPath) {
                if ('Pornhub' == $result['library']) {
                    $result['library'] = 'New';
                }
                unset($result['name']);
                //            unset($result['library']);
                if (null === $result['path'] || '' == $result['path']) {
                    //                  return false;
                    unset($result['path']);
                } else {
                    unset($result['studio']);
                }

                $path = implode('/', $result);

                return rtrim($path, '/');
            } else {
                return $result['studio'];
            }

            // return rtrim($result['path'], '/');
        }
        $this->addStudioToMap($tag, ['library' => 'New', 'name' => $queryTerm, 'studio' => $text, 'path' => null]);

        return false;
    }

    public function addStudioToMap($tag, $array)
    {
        if (Option::istrue('sublibrary')) {
            $array['library'] = Option::getValue('sublibrary');
        }
        $library = trim("'" . $array['library'] . "'");
        $name    = trim("'" . $array['name'] . "'");
        $studio  = trim("'" . $array['studio'] . "'");

        if (null !== $array['path']) {
            $path = "'" . $array['path'] . "'";
        } else {
            $path = 'NULL';
        }

        $query   = 'INSERT IGNORE INTO ' . __MYSQL_STUDIOS__ . '  (library, name, studio, path) VALUES (' . $library . ',' . $name . ',' . $studio . ', ' . $path . ') ';
        $query   = $query . ' ON DUPLICATE KEY UPDATE library=' . $library . ',studio=' . $studio . ',path=' . $path;
        // utmdump($query);
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
