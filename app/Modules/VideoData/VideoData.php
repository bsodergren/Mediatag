<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData;

use Mediatag\Core\Mediatag;
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
        return $this->get($this->video_key, $this->video_file);
    }

    public function saveVideoDetails()
    {
        return $this->save();
    }

    public function getVideoText()
    {
        return $this->getText();
    }

    public function getVideoInfo($key, $file)
    {
        $this->video_file = $file;
        $this->video_key = $key;
        $exists = Mediatag::$dbconn->videoExists($key, null, $this->VideoFileTable);
        if (null === $exists) {
            $data_array = Mediatag::$dbconn->createDbEntry($file, $key);
            Mediatag::$dbconn->insert($data_array);
        }

        $this->VideoInfo = $this->getVideoDetails();

        return $this->saveVideoDetails();
    }

    public function updateVideoData()
    {
        $file_array = $this->getDbList();

        if (\count($file_array) > 0) {
            foreach ($file_array as $key => $file) {
                if (file_exists($file)) {
                    $this->getVideoInfo($key, $file);
                    if (! Option::istrue('all')) {
                        $int = $this->resultCount--;
                        $int = str_pad($int, 4, ' ', \STR_PAD_LEFT);
                        Mediatag::$output->writeln('<info>'.$int.'</info> : '.$this->getVideoText());
                    }
                }
            }
        }
    }

    public function clearDBValues($key = null)
    {
        $query = $this->clearQuery($key);
        $result = Mediatag::$dbconn->query($query);
    }

    public function getDbList()
    {
        $file_array = [];

        $query = $this->videoQuery();
        $result = Mediatag::$dbconn->query($query);

        //
        foreach ($result as $_ => $row) {
            $file_array[$row['video_key']] = $row['file_name'];
        }
        $this->resultCount = \count($file_array);

        return $file_array;
    }

    public function save()
    {
        $this->VideoInfo['video_key'] = $this->video_key;
        $this->VideoInfo['library'] = __LIBRARY__;

        if (Mediatag::$dbconn->insert($this->VideoInfo, $this->VideoDataTable)) {
            // $this->returnText = '<comment>Updated</comment> '.$this->videoData;

            return $this->getVideoText();
        }
    }

    public function get($key, $file)
    {
    }

    public function clean()
    {
    }

    public function videoQuery()
    {
    }

    public function clearQuery($key = null)
    {
    }

    public function getText()
    {
        return '';
    }
}
