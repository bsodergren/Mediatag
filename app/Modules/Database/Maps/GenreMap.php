<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database\Maps;

trait GenreMap
{
    public function addGenreToMap($tag, $text)
    {
        $table = $this->getTagTable($tag);
        $key   = $this->makeKey($text);

        if ($text == '') {
            return;
        }

        $query = 'INSERT IGNORE INTO ' . $table . '  (' . $tag . ", replacement, keep,new) VALUES ('" . $key . "','" . $text . "', 1,1)";

        $this->query($query);
    }

    public function lookupGenre($tag, $string, $bypass = false)
    {
        $table  = $this->getTagTable($tag);
        $where  = $this->getTagWhere($tag, $string);
        $query  = 'SELECT * FROM ' . $table . ' WHERE ' . $where;
        $result = $this->query($query);

        if (is_array($result)) {
            if (count($result) == 0) {
                $this->addTag($tag, $string);

                return $string;
            }
            if (count($result) > 1) {
                utmdd($result);

                return $result;
            }
            if ($result[0]['keep'] == 0 && $bypass === false) {
                return '';
            }

            if ($result[0]['replacement'] != '') {
                $text = $result[0]['replacement'];
            } else {
                $text = $result[0]['genre'];
            }
        }

        return $text;
    }
}
