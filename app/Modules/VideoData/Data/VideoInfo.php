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
    public $video_name;

    public $resultCount;

    public $VideoInfo;
    public $thumbType = "info";

    public $maxLen  = 75;

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

        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($this->video_file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();

        foreach ($videos as $video) {
            $videoInfo['format']   = (string) $general->get('format');
            $videoInfo['bit_rate'] = (string) $video->get('bit_rate')->getAbsoluteValue();

            $videoInfo['width']  = (string) $video->get('width')->getAbsoluteValue();
            $videoInfo['height'] = (string) $video->get('height')->getAbsoluteValue();
        }

        if (!isset($videoInfo)) {
            $videoInfo['format']   = null;
            $videoInfo['bit_rate'] = null;
            $videoInfo['width']    = null;
            $videoInfo['height']   = null;
            Mediatag::$output->writeln('<error>file is corrupt: '.$this->video_file.'</error> ');

            // utmdump("something wrong with " . $this->video_file);
        }

        return $videoInfo;
    }



    public static function compareDupes($file, $sfile)
    {
        $return = 'A';

        $video1Info = self::getVidInfo($file);
        $video2Info = self::getVidInfo($sfile);
        $keys       = ['duration', 'bit_rate', 'filesize'];
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
        $mediaInfo             = new MediaInfo();
        $mediaInfoContainer    = $mediaInfo->getInfo($file);
        $videos                = $mediaInfoContainer->getVideos();
        $general               = $mediaInfoContainer->getGeneral();
        $videoinfo['file']     = $file;
        $videoinfo['filesize'] = filesize($file);
        foreach ($videos as $video) {
            $videoinfo['format']   = (string) $general->get('format');
            $videoinfo['bit_rate'] = (string) $video->get('bit_rate')->getAbsoluteValue();
            $videoinfo['width']    = (string) $video->get('width')->getAbsoluteValue();
            $videoinfo['height']   = (string) $video->get('height')->getAbsoluteValue();

            if (
                null !== $video->get('source_duration')
                && \array_key_exists('0', $video->get('source_duration'))
            ) {
                $videoinfo['duration'] = (string) $video->get('source_duration')[0];
            } else {
                $videoinfo['duration'] = (string) $video->get('duration');
            }
        }

        return $videoinfo;
    }
}
