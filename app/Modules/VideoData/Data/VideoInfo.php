<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Traits\ffmpeg;
use Mhor\MediaInfo\MediaInfo;

class VideoInfo extends VideoData
{
    use ffmpeg;

    public $video_key;

    public $video_file;

    public $resultCount;

    public $VideoInfo;

    public $VideoDataTable = __MYSQL_VIDEO_INFO__;

    private $actionText    = "<comment>Updated Video Data</comment>";

    public function getText()
    {
        utminfo(func_get_args());

        return $this->actionText . ' for ' . basename($this->video_file);

    }

    public function get($key, $file)
    {
        utminfo(func_get_args());

        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($this->video_file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();

        foreach ($videos as $video) {
            $videoInfo['format']   = (string) $general->get('format');
            $videoInfo['bit_rate'] = (string) $video->get('bit_rate')->getAbsoluteValue();

            $videoInfo['width']    = (string) $video->get('width')->getAbsoluteValue();
            $videoInfo['height']   = (string) $video->get('height')->getAbsoluteValue();
        }

        if (!isset($videoInfo)) {
            $videoInfo['format']   = null;
            $videoInfo['bit_rate'] = null;
            $videoInfo['width']    = null;
            $videoInfo['height']   = null;
            Mediatag::$output->writeln('<error>file is corrupt: ' . $this->video_file . '</error> ');

            //utmdump("something wrong with " . $this->video_file);
        }
        return $videoInfo;
    }

    public function videoQuery()
    {
        utminfo(func_get_args());

        $sql = "SELECT CONCAT(f.fullpath,'/',f.filename) as file_name, f.video_key ";
        $sql .= 'FROM ' . $this->VideoFileTable . ' f ';
        $sql .= 'LEFT OUTER JOIN ' . $this->VideoDataTable . ' i on f.video_key=i.video_key ';
        $sql .= " WHERE i.width  is null and f.library = '" . __LIBRARY__ . "'";

        return $sql;
    }

    public function clearQuery($key = null)
    {
        utminfo(func_get_args());

        $where = '';
        if (null !== $key) {
            $exists = Mediatag::$dbconn->videoExists($key, null, $this->VideoDataTable);
            if (null !== $exists) {
                $where = "AND video_key = '" . $key . "'";
            }
        }

        return 'delete from ' . $this->VideoDataTable . ' WHERE Library = "' . __LIBRARY__ . '" ' . $where;
    }
}
