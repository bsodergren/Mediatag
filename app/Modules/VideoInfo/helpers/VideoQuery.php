<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\helpers;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Utilities\Option;

use function count;

trait VideoQuery
{
    public function getDbList()
    {
        // utminfo(func_get_args());

        $file_array = [];
        if (Option::istrue('filelist')) {
            $fileList = Mediatag::$SearchArray;
            foreach ($fileList as $filename) {
                $key              = MediaFile::getVideoKey($filename);
                $file_array[$key] = $filename;
            }
            $this->resultCount = count($file_array);

            return $file_array;
            //            utmdd( $file_array);
        }
        $query = $this->videoQuery();

        if (!Option::istrue('clean')) {
            if (Option::isTrue('max')) {
                $total = (int) Option::getValue('max');
                $query = $query . ' LIMIT ' . $total;
            }
        }

        Mediatag::info("Query", $query);
        $result = Mediatag::$dbconn->query($query);

        foreach ($result as $_ => $row) {
            if ($this->thumbType == 'markers') {
                $file_array[$row['video_key']] = $row;

                continue;
            }
            $file_array[$row['video_key']] = $row['file_name'];
        }

        $this->resultCount = count($file_array);

        return $file_array;
    }

    public function videoQuery($video_id = null, $search = null)
    {
        if ($this->thumbType == 'info') {
            return $this->InfoVideoQuery($video_id);
        }
        if ($this->thumbType == 'markers') {
            return $this->MarkersVideoQuery($video_id, $search);
        }
        $searchPath = ' AND fullpath like \'' . __CURRENT_DIRECTORY__ . '%\' ';

        $where = $this->thumbType . ' is null ';

        if (Option::istrue('update') || Option::istrue('clear')) {
            $where = $this->thumbType . ' is not null ';
        }

        if ($this->thumbType == 'duration') {
            $where = ' (duration is null or duration < 50) ';
        }

        $where .= $searchPath;

        $query = "SELECT CONCAT(fullpath,'/',filename) as file_name, video_key FROM " . $this->VideoDataTable . " WHERE  Library = '" . __LIBRARY__ . "' AND  " . $where;

        return $query;
    }

    public function clearQuery($key = null)
    {
        // utminfo(func_get_args());

        $where = '';
        if ($key !== null) {
            $exists = Mediatag::$dbconn->videoExists($key, null, $this->VideoDataTable);
            if ($exists !== null) {
                $where = "AND video_key = '" . $key . "'";
            }
        }

        return 'update ' . $this->VideoDataTable . ' set ' . $this->getTableField() . ' = null WHERE Library = "' . __LIBRARY__ . '"' . $where;
    }

    private function InfoVideoQuery($video_id = null)
    {
        $searchPath  = ' AND fullpath like \'' . __CURRENT_DIRECTORY__ . '%\' ';
        $sql         = "SELECT CONCAT(f.fullpath,'/',f.filename) as file_name, f.video_key ";
        $sql        .= 'FROM ' . $this->VideoFileTable . ' f ';
        $sql        .= 'LEFT OUTER JOIN ' . $this->VideoDataTable . ' i on f.video_key=i.video_key ';
        $sql        .= " WHERE i.width  is null and f.library = '" . __LIBRARY__ . "' " . $searchPath;

        return $sql;
    }

    private function MarkersVideoQuery($video_id = null, $search = null)
    {
        $fields = " CONCAT(f.fullpath,'/',f.filename) as filename, f.video_key, vm.timeCode, vm.id, i.duration ";
        $order  = '';
        if ($video_id === null) {
            $where = ' vm.markerThumbnail is null ';
        } else {
            $where   = ' vm.video_id =  ' . $video_id . ' ';
            $fields .= ', vm.markerText ';
            $order   = ' ORDER BY `vm`.`timeCode` ASC';
        }
        if (Option::istrue('update')) {
            $where = ' vm.markerThumbnail is not null ';
        }

        $where .= ' AND f.video_key = i.video_key AND f.id = vm.video_id AND f.fullpath like \'' . __CURRENT_DIRECTORY__ . '%\' ';
        if ($search !== null) {
            $where .= ' AND  vm.markerText like "' . $search . '%" ';
        }

        $sql = 'SELECT ' . $fields . ' FROM ' . $this->VideoDataTable . ' vm, ' . __MYSQL_VIDEO_FILE__ . ' f, ' . __MYSQL_VIDEO_INFO__ . ' i WHERE ' . $where . $order;

        return $sql;
    }
}
