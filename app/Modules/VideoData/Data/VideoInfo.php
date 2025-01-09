<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Modules\VideoData\VideoData;
use Mhor\MediaInfo\MediaInfo;

class VideoInfo extends VideoData
{
    public $video_key;

    public $video_file;
    public $video_name;

    public $resultCount;

    public $VideoInfo;
    public $thumbType = 'info';

    public $maxLen = 75;

    public $VideoDataTable = __MYSQL_VIDEO_INFO__;

    public $actionText = '<info>Updated Video Data</info>';

    // public function getText()
    // {
    //     // utminfo(func_get_args());

    //     return $this->actionText; // . ' for ' . basename($this->video_file);
    // }

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        return self::getVidInfo($file);
    }

    public static function compareDupes($file, $sfile)
    {
        $return = 'A';

        $video1Info         = self::getVidInfo($file);
        $video1Info['file'] = $file;

        $video2Info         = self::getVidInfo($sfile);
        $video2Info['file'] = $sfile;

        $keys = ['duration', 'bit_rate', 'filesize'];
        foreach ($keys as $key) {
            if ($video1Info[$key] > $video2Info[$key]) {
                $return = 'A';
            } elseif ($video1Info[$key] < $video2Info[$key]) {
                $return = 'B';
            }
        }

        if ('B' == $return) {
            return [$video2Info['file'], $video1Info['file']];
        } else {
            return [$video1Info['file'], $video2Info['file']];
        }
    }

    public static function getVidInfo($file)
    {
        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();
        $audios             = $mediaInfoContainer->getAudios();

        // $videoInfo['file']     = $file;
        $videoInfo['filesize'] = filesize($file);
        foreach ($audios as $audio) {
            $videoInfo['codec_type'] = (string) $audio->get('kind_of_stream');
        }
        foreach ($videos as $video) {
            $videoInfo['format'] = (string) $general->get('format');
            $bit_rate            = $video->get('bit_rate');
            if (null === $bit_rate) {
                $bit_rate = $video->get('maximum_bit_rate');
            }

            $videoInfo['bit_rate'] = (string) $bit_rate->getAbsoluteValue();
            $videoInfo['width']    = (string) $video->get('width')->getAbsoluteValue();
            $videoInfo['height']   = (string) $video->get('height')->getAbsoluteValue();
            $videoInfo['duration'] = $video->get('duration')->getMilliseconds();
        }

        utmdump($videoInfo);

        return $videoInfo;
    }
}
