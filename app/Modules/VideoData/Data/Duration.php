<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Traits\ffmpeg;
use Mhor\MediaInfo\MediaInfo;

class Duration extends VideoData
{
    use ffmpeg;

    public $video_key;

    public $video_file;
    public $video_name;

    public $returnText;

    public $resultCount;
    public $thumbType = 'duration';

    public $maxLen = 75;

    public $VideoInfo;
    public $actionText     = '<info>Updated Duration</info>';
    public $VideoDataTable = __MYSQL_VIDEO_FILE__;

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = $key;

        $this->VideoInfo = $this->getVideoDuration();

        return ['duration' => $this->VideoInfo['duration'], 'video_key' => (string) $this->video_key];
    }

    public function getVideoDuration()
    {
        // utminfo(func_get_args());

        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($this->video_file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();

        foreach ($videos as $video) {
            if (
                null !== $video->get('source_duration')
                && \array_key_exists('0', $video->get('source_duration'))
            ) {
                $videoInfo['duration'] = (string) $video->get('source_duration')[0];
            } else {
                $videoInfo['duration'] = (string) $video->get('duration');
            }
        }

        if (!isset($videoInfo)) {
            $videoInfo['duration'] = null;
            Mediatag::$output->writeln('<error>file is corrupt: '.$this->video_file.'</error> ');
        }

        return $videoInfo;
    }
}
