<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Traits\DbMap;
use Mediatag\Modules\Database\Traits\StorageDB;
use Mediatag\Modules\Database\Traits\TagDB;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Utilities\Strings;
use PDO;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function get_class;
use function is_array;

class Storage
{
    use DbMap;
    use StorageDB;
    use TagDB;

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

    public $mysqllib = null;

    public object $mapClass;

    public static $DB = null;

    protected $DbConnection;

    public $MultiIDX = 1;

    public function __construct(?MysqliDb $DbConnection = null)
    {
        $db = MysqliDb::getInstance();
        if ($DbConnection === null) {
            $DbConnection = $db;
        }

        $this->mysqllib = $DbConnection; //->getInstance();
        $this->mysqllib->setTrace(true);
        self::$DB = $this;

        //  utmdd($this->mysqllib,self::$DB);

        if (! is_null(Mediatag::$output)) {
            $this->output      = Mediatag::$output;
            $this->input       = Mediatag::$input;
            $this->FileNumber  = $this->output->section();
            $this->headerBlock = $this->output->section();
            $this->RowBlock    = $this->output->section();
        }
    }

    public function trace()
    {
        // utmdump($this->mysqllib->trace);
    }

    public function truncate()
    {
        // utminfo(func_get_args());

        foreach (__MYSQL_TRUNC_TABLES__ as $table) {
            $res[] = $this->mysqllib->rawQuery('TRUNCATE ' . $table);
        }

        // utmdd([__METHOD__,$res]);
    }

    //   public function __call($name, $arguments)
    //     {
    //         $tag = strtolower(\str_replace('get', '', $name));

    //     }

    public function getGenre($arguments)
    {
        return $this->getTag('genre', $arguments);
    }

    public function __call($name, $arguments)
    {
        // utminfo(func_get_args());

        $method = $name;
        utmdump(['Name' => $name]);

        // Note: value of $name is case sensitive.
        if (str_contains($name, 'Genre')) {
            $tag    = 'genre';
            $method = str_replace('Genre', 'Tag', $name);
            // return $this->getTag($tag, $arguments);
        }
        if (str_contains($name, 'Keyword')) {
            $tag    = 'keyword';
            $method = str_replace('Keyword', 'Tag', $name);
        }

        if (method_exists($this, $method)) {
            return $this->{$method}($tag, ...$arguments);
        }
        // if (method_exists(get_class($this->mysqllib), $method)) {
        //     return $this->mysqllib->{$method}(...$arguments);
        // }
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
            if (! is_array($where[0])) {
                if (! array_key_exists('field', $where)) {
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

        if ($test === true) {
            $this->mysqllib->startTransaction();
        }

        foreach ($where as $row => $query) {
            if (str_contains($query['value'], 'null')) {
                $condition = trim(str_replace('null', '', $query['value']));
                $this->mysqllib->where($query['field'], null, strtoupper($condition));

                continue;
            }
            if (str_contains($query['value'], 'like')) {
                $condition = trim(str_replace('like', '', $query['value']));
                $this->mysqllib->where($query['field'], null, $condition);

                continue;
            }
        }

        // utmdd($where);

        $ret = $this->mysqllib->delete($table);
        if ($test === true) {
            $this->mysqllib->rollback();

            return $ret;
            //     utmdd($res);
        }
        $this->mysqllib->commit();

        return $ret;
    }

    public function query($sql, $numRows = null)
    {
        // utminfo(func_get_args());
        $res = $this->mysqllib->rawQuery($sql);

        return $res;
    }

    public function queryOne($sql, $numRows = null)
    {
        // utminfo(func_get_args());
        return $this->mysqllib->rawQueryOne($sql);
    }

    public function videoExists($video_key, $where = null, $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());
        $this->mysqllib->where('video_key', $video_key);
        $this->mysqllib->where('library', __LIBRARY__);
        if ($where !== null) {
            $this->mysqllib->where($where, null, 'IS');
        }

        $ret = $this->mysqllib->getOne($table);

        // utmdd($ret);
        // // utmdump([$this->mysqllib->getLastQuery(),__LIBRARY__,$ret]);
        return $ret;
    }

    public function insertVideoDb($data)
    {
        // utminfo(func_get_args());

        foreach ($data as $videokey => $rowData) {
            $this->mysqllib->startTransaction();
            $commit = true;
            foreach ($rowData as $tableName => $data) {
                if ($commit === true) {
                    if (! $this->mysqllib->insert($tableName, $data)) {
                        $this->video_string[] = ['insert failed: ' . $this->mysqllib->getLastError()];
                        // Error while saving, cancel new record
                        $this->mysqllib->rollback();
                        $commit = false;
                    }
                }
            }
            if ($commit === true) {
                $this->mysqllib->commit();
            }
        }
        $this->RowBlock->overwrite($this->video_string);
    }

    public function insertMulti($data, $table = __MYSQL_VIDEO_FILE__, $quiet = false)
    {
        // utminfo(func_get_args());
        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__ . ' -> ' . $array_string);

            return false;
        }

        $ids = $this->mysqllib->insertMulti($table, $data);
        if (! $ids) {
            $this->video_string = ['insert failed: ' . $this->mysqllib->getLastError()];
        }
        if ($quiet === false) {
            $this->RowBlock->overwrite($this->video_string);
        }
    }

    public function update($data, $where = [], $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());

        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__ . ' -> ' . $array_string);
            $array_string = var_export($where, 1);
            $this->output->writeln(__METHOD__ . ' -> ' . $array_string);

            return false;
        }

        foreach ($where as $field => $value) {
            $this->mysqllib->where($field, $value);
        }

        // $data = array_merge($data, $where);

        // if(array_key_exists('video_key',$fieldArray)){
        //  //   unset($fieldArray['video_key']);
        // }

        // $this->mysqllib->onDuplicate($data, 'id');
        // $id = $this->mysqllib->insert($table, $data);
        $id = $this->mysqllib->update($table, $data);
        // UtmDump($this->mysqllib->getLastQuery());
        if (! $id) {
            $this->video_string = ['insert failed: ' . $this->mysqllib->getLastQuery()];

            // return $r;
        }
        // $this->RowBlock->writeln($this->video_string);

        // return $r;
    }

    public function getValue($where_clause, $column = 'count(*)', $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());
        utmdump($this->mysqllib->trace);
        if (is_array($where_clause)) {
            foreach ($where_clause as $field => $where) {
                $this->mysqllib->where($field, $where[0], $where[1]);
            }
        }

        return $this->mysqllib->getValue($table, $column);
        // // UTMlog::Logger('INSERT SQL', $this->mysqllib->getLastQuery());
    }

    public function insert($data, $table = __MYSQL_VIDEO_FILE__)
    {
        // utminfo(func_get_args());

        $id = null;
        if (Option::Istrue('test')) {
            $array_string = var_export($data, 1);
            $this->output->writeln(__METHOD__ . ' -> ' . $array_string);

            return false;
        }

        // try {
        $fieldArray = $data;
        if (array_key_exists('fullpath', $fieldArray)) {
            $has = $this->mysqllib->where('video_key', $fieldArray['video_key'])->getOne($table);

            if ($has !== null) {
                $backup_path = str_replace('XXX/', 'XXX/Dupes/', $fieldArray['fullpath']);
                if (! Mediatag::$filesystem->exists($backup_path)) {
                    Mediatag::$filesystem->mkdir($backup_path);
                }
                $old_file = $fieldArray['fullpath'] . '/' . $fieldArray['filename'];
                $new_file = $backup_path . '/' . $fieldArray['filename'];

                // // utmdump($old_file, $new_file);
                Mediatag::$filesystem->rename($old_file, $new_file);

                return null;
            }
        }

        $dupCols = array_keys($fieldArray);
        // utmdd($dupCols);
        //     unset();
        // }

        $this->mysqllib->onDuplicate($dupCols, 'id');
        $id = $this->mysqllib->insert($table, $data);

        // utmdump($this->mysqllib->getLastQuery());
        // } catch (\Exception $e) {

        // }

        return $id;

        // // UTMlog::Logger('INSERT SQL', $this->conn->getLastQuery());
    }

    public function makeKey($text)
    {
        // utminfo(func_get_args());

        $key = strtolower($text);

        // /*
        $key = trim($key);
        $key = str_replace(' ', '_', $key);
        $key = str_replace('/', '', $key);
        $key = str_replace('+', '', $key);
        $key = str_replace('(', '', $key);

        // */
        $txt = Strings::clean($key, true);

        return $txt;
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
            case 'artist':
                return __MYSQL_ARTISTS__;
        }
    }

    protected function queryBuilder($query_cmd, $search = null, $limit = false, $allfiles = false)
    {
        // utminfo(func_get_args());

        $sel_cols     = null;
        $die          = false;
        $where        = null;
        $where_clause = [];

        $current_dir = (new Filesystem)->makePathRelative(__CURRENT_DIRECTORY__, __PLEX_HOME__);
        $current_dir = rtrim($current_dir, '/');
        // utmdump($current_dir);
        switch ($query_cmd) {
            case 'select':
                if ($search === null) {
                    $search = '*';
                }
                $query = 'SELECT ' . $search . ' FROM ';

                break;

            case 'delete':
                $query = 'delete from ';
                if (is_array($search)) {
                    $search = ' ' . $search[0] . " = '" . $search[1] . "'";
                }
                $sel_cols = $search;
                $die      = true;

                break;

            case 'cleandb':

                $field = 'f';
                if (Option::getValue('type') == 'meta') {
                    $field = 'm';
                }
                if (Option::getValue('type') == 'info') {
                    $field = 'i';
                }

                $cleanQuery = 'DELETE ' . $field . ' FROM ' . __MYSQL_VIDEO_FILE__ . ' as f  ';
                if (Option::getValue('type') == 'meta') {
                    $cleanQuery .= ', ' . __MYSQL_VIDEO_METADATA__ . ' as m  ';
                }
                if (Option::getValue('type') == 'info') {
                    $cleanQuery .= ', ' . __MYSQL_VIDEO_INFO__ . ' as i  ';
                }
                $cleanQuery .= " WHERE f.Library = '" . __LIBRARY__ . "' AND ";
                $cleanQuery .= " f.fullpath like '%" . $current_dir . "%'";
                if (Option::getValue('type') == 'meta') {
                    $cleanQuery .= ' and ( m.video_key = f.video_key )';
                }
                if (Option::getValue('type') == 'info') {
                    $cleanQuery .= ' and (i.video_key = f.video_key) ';
                }
                // utmdd($cleanQuery);

                return $cleanQuery;

            default:
                exit('No command added');

                // break;
        }

        $query .= __MYSQL_VIDEO_FILE__ . " WHERE Library = '" . __LIBRARY__ . "'  ";

        if (Option::isTrue('filelist')) {
            if ($this->file_array !== null && count($this->file_array) > 0) {
                foreach ($this->file_array as $key => $file) {
                    $where_clause[] = " video_key = '" . $key . "'";
                }

                if (count($where_clause) > 1) {
                    $where  = '( ';
                    $string = implode(' OR ', $where_clause);
                    $where  = $where . $string . ') ';
                } else {
                    $where = $where_clause[0];
                }
            }
        } else {
            if ($allfiles === false) {
                $where = " fullpath like '%" . $current_dir . "%'";
            }
        }
        if ($sel_cols !== null) {
            $where = $sel_cols;
        }
        if ($limit == true) {
            $limit = ' LIMIT ' . $limit;
        }
        if ($where !== null) {
            $where = ' AND ' . $where;
        }
        $sql = $query . $where . $limit;

        if (Option::Istrue('test')) {
            Mediatag::$output->writeln($sql);
            if ($die == true) {
                exit;
            }
        }

        return $sql;
    }
}
