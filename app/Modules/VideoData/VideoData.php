<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFinder;
use UTM\Utilities\Option;

class VideoData
{
    public $video_key;

    public $video_file;

    public $returnText;

    public $resultCount;

    public $VideoInfo;

    public $VideoDataTable;
    public $VideoFileTable = __MYSQL_VIDEO_FILE__;

    public function getVideoDetails()
    {
        utminfo(func_get_args());

        return $this->get($this->video_key, $this->video_file);
    }

    public function saveVideoDetails()
    {
        utminfo(func_get_args());

        return $this->save();
    }

    public function getVideoText()
    {
        utminfo(func_get_args());

        return $this->getText();
    }

    public function getVideoInfo($key, $file)
    {
        utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = $key;
        $exists           = Mediatag::$dbconn->videoExists($key, null, $this->VideoFileTable);
        if (null === $exists) {
            $data_array = Mediatag::$dbconn->createDbEntry($file, $key);
            Mediatag::$dbconn->insert($data_array);
        }

        $this->VideoInfo  = $this->getVideoDetails();

        return $this->saveVideoDetails();
    }

    public function updateVideoData()
    {
        utminfo(func_get_args());

        $file_array = $this->getDbList();

        if (\count($file_array) > 0) {
            foreach ($file_array as $key => $file) {
                if (file_exists($file)) {
                    $res= $this->getVideoInfo($key, $file);
                    if (! Option::istrue('all')) {
                        $int = $this->resultCount--;
                        $int = str_pad($int, 4, ' ', \STR_PAD_LEFT);
                        if ($res !== false) {
                            Mediatag::$output->writeln('<info>' . $int . '</info> : ' . $this->getVideoText());
                        }
                    }
                }
            }
        }
    }

    public function clearDBValues($key = null)
    {
        utminfo(func_get_args());

        $query  = $this->clearQuery($key);
        $result = Mediatag::$dbconn->query($query);
    }

    public function getDbList()
    {
        utminfo(func_get_args());

        $file_array        = [];
        if (Option::istrue('filelist')) {

            $fileList          = Mediatag::$SearchArray;
            foreach ($fileList as $filename) {

                $key              = MediaFile::getVideoKey($filename);
                $file_array[$key] = $filename;
            }
            $this->resultCount = \count($file_array);

            return $file_array;
            //            utmdd( $file_array);

        }
        $query             = $this->videoQuery();

        if (!Option::istrue('clean')) {
            if (Option::isTrue('max')) {
                $total = (int) Option::getValue('max');
                $query = $query . " LIMIT " . $total;
            }
        }

        $result            = Mediatag::$dbconn->query($query);

        //
        foreach ($result as $_ => $row) {
            $file_array[$row['video_key']] = $row['file_name'];
        }
        $this->resultCount = \count($file_array);


        return $file_array;
    }

    public function save()
    {
        utminfo(func_get_args());

        $this->VideoInfo['video_key'] = $this->video_key;
        $this->VideoInfo['library']   = __LIBRARY__;


        if (array_key_exists('duration', $this->VideoInfo)) {
            if ($this->VideoInfo['duration'] === null) {
                return false;
            }
        }
        if (array_key_exists('format', $this->VideoInfo)) {
            if ($this->VideoInfo['format'] === null) {
                return false;
            }
        }
        if (Mediatag::$dbconn->insert($this->VideoInfo, $this->VideoDataTable)) {
            // $this->returnText = '<comment>Updated</comment> '.$this->videoData;

            return $this->getVideoText();
        }
    }

    public function get($key, $file)
    {
        utminfo(func_get_args());

    }

    public function clean()
    {
        utminfo(func_get_args());

    }

    public function videoQuery()
    {
        utminfo(func_get_args());

    }

    public function clearQuery($key = null)
    {
        utminfo(func_get_args());

    }

    public function getText()
    {
        utminfo(func_get_args());

        return '';
    }
}
