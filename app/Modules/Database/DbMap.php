<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use UTM\Bundle\mysql\MysqliDb;
use Mediatag\Modules\Database\Maps\ArtistMap;
use Mediatag\Modules\Database\Maps\StudioMap;
use Mediatag\Modules\Database\Maps\TitleMap;
use Mediatag\Modules\VideoData\Data\Thumbnail;

class DbMap extends Storage
{
    use ArtistMap;
    use StudioMap;
    use TitleMap;

    public function __construct()
    {
        // utminfo(func_get_args());

        $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
    }

    public function getVideoCount()
    {
        // utminfo(func_get_args());

        $query   = $this->queryBuilder('select', 'COUNT(*) as count');
        $results = $this->query($query);

        return $results[0]['count'];
    }

    public function emptydatabase()
    {
        // utminfo(func_get_args());

        $query   = $this->queryBuilder('cleandb');
        $results = $this->query($query);
        // $thumb = Thumbnail::videoToThumb(__CURRENT_DIRECTORY__);
        // FileSystem::delete($thumb);
    }

    public function listTag($tag)
    {
        // utminfo(func_get_args());

        $table  = $this->getTagTable($tag);
        $result = $this->dbConn->get($table, null, $tag);
        if (\is_array($result)) {
            if (\count($result) > 1) {
                foreach ($result as $k => $v) {
                    $array[] = $v[$tag];
                }

                return $array;
            }
        } else {
            return null;
        }
    }

    public function addTag($tag, $text)
    {
        // utminfo(func_get_args());

        $table = $this->getTagTable($tag);
        $key   = $this->makeKey($text);
        $query = 'INSERT IGNORE INTO ' . $table . '  (' . $tag . ", replacement) VALUES ('" . $key . "','" . $text . "')";

        $this->dbConn->rawQuery($query);
    }

    public function getTag($tag, $string, $bypass = false)
    {
        // utminfo(func_get_args());

        $text   = $string;
        $table  = $this->getTagTable($tag);
        $where  = $this->getTagWhere($tag, $string);

        $query  = 'SELECT * FROM ' . $table . ' WHERE ' . $where;
        $result = $this->dbConn->rawQuery($query);

        if (\is_array($result)) {
            if (0 == \count($result)) {
                return false;
            }

            if (\count($result) > 1) {
                return $result;
            }

            if (0 == $result[0]['keep'] && false === $bypass) {
                return false;
            }
            if ('' != $result[0]['replacement']) {
                $text = $result[0]['replacement'];
            } else {
                $text = $result[0]['genre'];
            }
        }

        return $text;
    }

    public function addNewTagReplacement($tag, $text, $addition, $show = null)
    {
        // utminfo(func_get_args());

        $existing = $this->getTag($tag, $text, true);

        if (\is_array($existing)) {
            foreach ($existing as $i => $row) {
                $this->getReplacementString($tag, $row[$tag], $addition, $show);
            }
        } else {
            $this->getReplacementString($tag, $text, $addition, $show);
        }
    }

    public function getReplacementString($tag, $text, $addition, $show = null)
    {
        // utminfo(func_get_args());

        $existing = $this->getTag($tag, $text, true);

        $string   = $this->sortTagList($existing, $addition);

        $this->updateTag($tag, $text, $string, $show);
    }

    public function updateTag($tag, $text, $replacement, $show = null)
    {
        // utminfo(func_get_args());

        $table    = $this->getTagTable($tag);
        $where    = $this->getTagWhere($tag, $text);
        $existing = $this->getTag($tag, $text, true);

        $updates  = [];

        if (null !== $replacement) {
            $replacement = $this->sortTagList($replacement);

            $updates[]   = " replacement = '" . $replacement . "' ";
        }
        if (null !== $show) {
            $updates[] = ' keep = ' . $show . ' ';
        }

        $replace  = implode(',', $updates);
        if (false === $existing) {
            $this->addTag($tag, $text);
        }

        $query    = 'UPDATE ' . $table . ' SET ' . $replace . ' WHERE  ' . $where;
        $result   = $this->dbConn->rawQueryOne($query);
    }

    private function getTagWhere($tag, $text)
    {
        // utminfo(func_get_args());

        $key   = $this->makeKey($text);
        $where = $tag . " = '" . $key . "';";
        if (str_contains($text, '%')) {
            $where = $tag . " like '%" . $key . "%';";
        }

        return $where;
    }
}
