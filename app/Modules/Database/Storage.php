<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use Mediatag\Utilities\Strings;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function get_class;
use function is_array;

class Storage
{
    public $DbFileArray = [];

    public $input;

    /**
     * output.
     */
    public $output;

    public $VideoInfo;

    public $thumbnail;

    public $file_array;

    public $VideoData;

    public $video_string = [];

    public $video_file;

    public $video_path;

    public $video_key;

    public $video_name;

    public $formatter;

    public $FileNumber;

    public $RowBlock;

    public $headerBlock;

    public $dbConn;

    public object $mapClass;

    private $MultiIDX = 1;

    public function __construct()
    {
        // utminfo();

        $this->dbConn = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
        $this->dbConn->setTrace(true);

        $this->mapClass    = new DbMap();
        $this->output      = Mediatag::$output;
        $this->input       = Mediatag::$input;
        $this->FileNumber  = $this->output->section();
        $this->headerBlock = $this->output->section();
        $this->RowBlock    = $this->output->section();
    }

    public function trace()
    {
        utmdump($this->dbConn->trace);
    }

    public function truncate()
    {
        // utminfo(func_get_args());

        foreach (__MYSQL_TRUNC_TABLES__ as $table) {
            $res[] = $this->dbConn->rawQuery('TRUNCATE '.$table);
        }

        // utmdd([__METHOD__,$res]);
    }

    public function __call($name, $arguments)
    {
        // utminfo(func_get_args());

        $method = $name;
        // Note: value of $name is case sensitive.
        if (str_contains($name, 'Genre')) {
            $tag    = 'genre';
            $method = str_replace('Genre', 'Tag', $name);
        }
        if (str_contains($name, 'Keyword')) {
            $tag    = 'keyword';
            $method = str_replace('Keyword', 'Tag', $name);
        }

        if (method_exists($this, $method)) {
            return $this->{$method}($tag, ...$arguments);
        }
        if (isset($this->mapClass)) {
            if (method_exists(get_class($this->mapClass), $method)) {
                return $this->mapClass->{$method}();
            }
        }

        // utmdd([__METHOD__,'DB method doesnt exist', $method, $arguments]);
    }

    public function delete($table, $where = [], $test = false)
    {
        if (array_key_exists(0, $where)) {
            if (!is_array($where[0])) {
                if (!array_key_exists('field', $where)) {
                    $field = $where[0];
                    $value = $where[1];
                    unset($where);
                    $where = ['field' => $field, 'value' => $value];
                }
            }
        }

        if (array_key_exists('field', $where)) {
            $tmp[] = $where;
            unset($where);
            $where = $tmp;
            unset($tmp);
        }

        if (true === $test) {
            $this->dbConn->startTransaction();
        }

        foreach ($where as $row => $query) {
            if (str_contains($query['value'], 'null')) {
                $condition = trim(str_replace('null', '', $query['value']));
                $this->dbConn->where($query['field'], null, strtoupper($condition));
                continue;
            }
            if (str_contains($query['value'], 'like')) {
                $condition = trim(str_replace('like', '', $query['value']));
                $this->dbConn->where($query['field'], null, $condition);
                continue;
            }
        }

        // utmdd($where);

        $ret = $this->dbConn->delete($table);
        if (true === $test) {
            $this->dbConn->rollback();

            return $ret;
            //     utmdd($res);
        }
        $this->dbConn->commit();

        return $ret;
    }

    public function query($sql)
    {
        // utminfo(func_get_args());

        $res = $this->dbConn->rawQuery($sql);

        return $res;
    }

    public function queryOne($sql)
    {
        // utminfo(func_get_args());

        return $this->dbConn->rawQueryOne($sql);
    }

    public function videoExists($video_key, $where = null, $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());
        $this->dbConn->where('video_key', $video_key);
        $this->dbConn->where('library', __LIBRARY__);
        if (null !== $where) {
            $this->dbConn->where($where, null, 'IS');
        }

        $ret = $this->dbConn->getOne($table);

        // utmdd($ret);
        // utmdump([$this->dbConn->getLastQuery(),__LIBRARY__,$ret]);
        return $ret;
    }

    public function insertVideoDb($data)
    {
        // utminfo(func_get_args());

        foreach ($data as $videokey => $rowData) {
            $this->dbConn->startTransaction();
            $commit = true;
            foreach ($rowData as $tableName => $data) {
                if (true === $commit) {
                    if (!$this->dbConn->insert($tableName, $data)) {
                        $this->video_string[] = ['insert failed: '.$this->dbConn->getLastError()];
                        // Error while saving, cancel new record
                        $this->dbConn->rollback();
                        $commit = false;
                    }
                }
            }
            if (true === $commit) {
                $this->dbConn->commit();
            }
        }
        $this->RowBlock->overwrite($this->video_string);
    }

    public function insertMulti($data, $table = __MYSQL_VIDEO_FILE__, $quiet = false)
    {
        // utminfo(func_get_args());
        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__.' -> '.$array_string);

            return false;
        }

        $ids = $this->dbConn->insertMulti($table, $data);
        if (!$ids) {
            $this->video_string = ['insert failed: '.$this->dbConn->getLastError()];
        }
        if (false === $quiet) {
            $this->RowBlock->overwrite($this->video_string);
        }
    }

    public function update($data, $where = [], $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());

        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__.' -> '.$array_string);
            $array_string = var_export($where, 1);
            $this->output->writeln(__METHOD__.' -> '.$array_string);

            return false;
        }

        foreach ($where as $field => $value) {
            $this->dbConn->where($field, $value);
        }

        // $data = array_merge($data, $where);

        // if(array_key_exists('video_key',$fieldArray)){
        //  //   unset($fieldArray['video_key']);
        // }

        // $this->dbConn->onDuplicate($data, 'id');
        // $id = $this->dbConn->insert($table, $data);
        $id = $this->dbConn->update($table, $data);
        if (!$id) {
            $this->video_string = ['insert failed: '.$this->dbConn->getLastQuery()];

            // return $r;
        }
        $this->RowBlock->overwrite($this->video_string);

        // return $r;
    }

    public function getValue($where_clause, $column = 'count(*)', $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());

        foreach ($where_clause as $field=> $where) {
            $this->dbConn->where($field, $where[0], $where[1]);
        }

        return $this->dbConn->getValue($table, $column);
        // // UTMlog::Logger('INSERT SQL', $this->dbConn->getLastQuery());
    }

    public function insert($data, $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());

        $id = null;
        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__.' -> '.$array_string);

            return false;
        }

        // try {
        $fieldArray = $data;
        if (array_key_exists('fullpath', $fieldArray)) {
            $has = $this->dbConn->where('video_key', $fieldArray['video_key'])->getOne($table);

            if (null !== $has) {
                $backup_path = str_replace('XXX/', 'XXX/Dupes/', $fieldArray['fullpath']);
                if (!Mediatag::$filesystem->exists($backup_path)) {
                    Mediatag::$filesystem->mkdir($backup_path);
                }
                $old_file = $fieldArray['fullpath'].'/'.$fieldArray['filename'];
                $new_file = $backup_path.'/'.$fieldArray['filename'];

                // utmdump($old_file, $new_file);
                Mediatag::$filesystem->rename($old_file, $new_file);

                return null;
            }
        }
        //     unset();
        // }

        $this->dbConn->onDuplicate($fieldArray, 'id');
        $id = $this->dbConn->insert($table, $data);
        // } catch (\Exception $e) {

        // }

        return $id;

        // // UTMlog::Logger('INSERT SQL', $this->conn->getLastQuery());
    }

    public function makeKey($text)
    {
        // utminfo(func_get_args());

        $key = strtolower($text);

        /*
         $key = trim($key);
         $key = str_replace(' ', '_', $key);
         $key = str_replace('/', '', $key);
         $key = str_replace('+', '', $key);
         $key = str_replace('(', '', $key);
*/
        return Strings::clean($key);
        //        return str_replace(')', '', $key)."--";
    }

    public function getTagTable($tag)
    {
        // utminfo(func_get_args());

        switch ($tag) {
            case 'genre':
                return __MYSQL_GENRE__;

            case 'keyword':
                return __MYSQL_KEYWORD__;
        }
    }

    protected function queryBuilder($query_cmd, $search = null, $limit = false)
    {
        // utminfo(func_get_args());

        $sel_cols = null;
        $die      = false;
        $where    = null;

        switch ($query_cmd) {
            case 'select':
                if (null === $search) {
                    $search = '*';
                }
                $query = 'SELECT '.$search.' FROM ';

                break;

            case 'delete':
                $query = 'delete from ';
                if (is_array($search)) {
                    $search = ' '.$search[0]." = '".$search[1]."'";
                }
                $sel_cols = $search;
                $die      = true;

                break;

            case 'cleandb':
                $cleanQuery = 'DELETE f FROM '.__MYSQL_VIDEO_FILE__.' as f  ';
                // $cleanQuery .= ', ' . __MYSQL_VIDEO_METADATA__ . ' as m  ';
                // $cleanQuery .= ', ' . __MYSQL_VIDEO_INFO__ . ' as i  ';
                $cleanQuery .= " WHERE f.Library = '".__LIBRARY__."' AND ";
                $cleanQuery .= " f.fullpath like '".__CURRENT_DIRECTORY__."%'";
                // $cleanQuery .= " and m.video_key = f.video_key ";

                return $cleanQuery;

            default:
                exit('No command added');

                // break;
        }

        $query .= __MYSQL_VIDEO_FILE__." WHERE Library = '".__LIBRARY__."' AND ";

        if (Option::isTrue('filelist')) {
            foreach ($this->file_array as $key => $file) {
                $where_clause[] = " video_key = '".$key."'";
            }
            if (count($where_clause) > 1) {
                $where  = '( ';
                $string = implode(' OR ', $where_clause);
                $where  = $where.$string.') ';
            } else {
                $where = $where_clause[0];
            }
        } else {
            $where = " fullpath like '".__CURRENT_DIRECTORY__."%'";
        }
        if (null !== $sel_cols) {
            $where = $sel_cols;
        }
        if (true == $limit) {
            $limit = ' LIMIT '.$limit;
        }
        $sql = $query.$where.$limit;

        if (Option::Istrue('test')) {
            Mediatag::$output->writeln($sql);
            if (true == $die) {
                exit;
            }
        }

        return $sql;
    }
}
