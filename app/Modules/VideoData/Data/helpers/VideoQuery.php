<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data\helpers;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Utilities\Option;

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
            $this->resultCount = \count($file_array);

            return $file_array;
            //            utmdd( $file_array);
        }
        $query = $this->videoQuery();

        if (!Option::istrue('clean')) {
            if (Option::isTrue('max')) {
                $total = (int) Option::getValue('max');
                $query = $query.' LIMIT '.$total;
            }
        }
        $result = Mediatag::$dbconn->query($query);

        foreach ($result as $_ => $row) {
            $file_array[$row['video_key']] = $row['file_name'];
        }
        $this->resultCount = \count($file_array);

        return $file_array;
    }

    public function videoQuery()
    {
        // utminfo(func_get_args());

        $searchPath = '';
        $where      = $this->thumbType.' is null ';

        if (Option::istrue('update') || Option::istrue('clear')) {
            $where = $this->thumbType.' is not null ';
        }

        if ('duration' == $this->thumbType) {
            $where = ' (duration is null or duration < 50) ';
        }

        $searchPath = ' AND fullpath like \''.__CURRENT_DIRECTORY__.'%\' ';

        if ('info' == $this->thumbType) {
            $sql = "SELECT CONCAT(f.fullpath,'/',f.filename) as file_name, f.video_key ";
            $sql .= 'FROM '.$this->VideoFileTable.' f ';
            $sql .= 'LEFT OUTER JOIN '.$this->VideoDataTable.' i on f.video_key=i.video_key ';
            $sql .= " WHERE i.width  is null and f.library = '".__LIBRARY__."' ".$searchPath;

            return $sql;
        }
        $where .= $searchPath;

        $query = "SELECT CONCAT(fullpath,'/',filename) as file_name, video_key FROM ".$this->VideoDataTable." WHERE  Library = '".__LIBRARY__."' AND  ".$where;

        return $query;
    }

    public function clearQuery($key = null)
    {
        // utminfo(func_get_args());

        $where = '';
        if (null !== $key) {
            $exists = Mediatag::$dbconn->videoExists($key, null, $this->VideoDataTable);
            if (null !== $exists) {
                $where = "AND video_key = '".$key."'";
            }
        }

        return 'update '.$this->VideoDataTable.' set '.$this->thumbType.' = null WHERE Library = "'.__LIBRARY__.'"'.$where;
    }
}
