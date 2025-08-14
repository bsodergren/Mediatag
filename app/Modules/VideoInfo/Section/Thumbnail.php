<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section;

use FFMpeg\FFProbe;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Utilities\Strings;

use function dirname;

class Thumbnail extends VideoInfo
{
    use MediaFFmpeg;
    public $video_key;

    public $video_file;

    public $video_name;

    public $video_path;

    public $resultCount;

    // public $updatedText = '<info>Updated Thumbnail</info>';

    public $VideoDataTable = __MYSQL_VIDEO_FILE__;

    public $thumbType = 'thumbnail';
    public $maxLen    = 75;

    public $thumbExt = '.jpg';
    public $thumbDir = __INC_WEB_THUMB_DIR__;

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        // utmdump($file);
        $this->video_key = (string) $key;
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $thumbnail = $this->getThumbImg();

        return ['thumbnail' => $thumbnail, 'video_key' => $this->video_key];
    }

    public function getThumbImg()
    {
        // utminfo(func_get_args());

        $this->video_name = basename($this->video_file);
        $this->video_path = dirname($this->video_file);

        // $img_name = basename($this->video_name, '.mp4').'.jpg';
        $img_name = basename($this->videoToThumb($this->video_file));
        // $img_name     = Strings::cleanFileName($img_name,true);
        $img_web_path = (new Filesystem())->makePathRelative($this->video_path, __PLEX_HOME__);
        $img_location = __INC_WEB_THUMB_DIR__.'/'.$img_web_path;
        $img_file     = $img_location.$img_name;
        $img_url_path = __INC_WEB_THUMB_URL__.'/'.$img_web_path.$img_name;
        $action       = $this->updatedText;
        // $type         = $this->actionText;

        if (!file_exists($img_file)) {
            (new Filesystem())->mkdir($img_location);
            $ffprobe = FFProbe::create([
                'ffmpeg.binaries'  => '/home/bjorn/bin/ffmpeg',
                'ffprobe.binaries' => '/home/bjorn/bin/ffprobe']);
            $duration = $ffprobe->streams($this->video_file)->videos()->first()->get('duration');

            // utmdump($this->video_file,$ffprobe->streams($this->video_file)->videos()->first()->get('codec_name'));

            $time = '00:01:00.00';

            if ((int) $duration < 7000) {
                $time = '00:00:15.00';
            }
            if ((int) $duration < 1600) {
                $time = '00:00:05.00';
            }

            if ((int) $duration < 500) {
                $time = '00:00:01.00';
            }
            $time = self::videoDuration($duration, 10);

            // utmdd($duration,$time);
            $this->ffmegCreateThumb($this->video_file, $img_file, $time);

            $action = $this->newText;
        }

        $this->actionText = $action.$this->thumbType;

        return $img_url_path;
    }
}
