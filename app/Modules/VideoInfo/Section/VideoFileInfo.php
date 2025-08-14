<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section;

use Mediatag\Core\MediaCache;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\TagBuilder\File\Reader as FileReader;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mhor\MediaInfo\MediaInfo;

class VideoFileInfo extends VideoInfo
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

        $array = self::getVidInfo($file);
        unset($array['frame_count']);

        return $array;
    }

    public static function compareDupes($file, $sfile)
    {
        $return    = 'A';
        $file1     = new MediaFile($file);
        $file1Info = $file1->get();
        $tag1      = (new VideoTags())->get($file1Info['video_key'], $file);
        $f1        = new FileReader($file1Info);
        $genre1Dir = $f1->getGenre();

        $file2     = new MediaFile($sfile);
        $file2Info = $file2->get();
        $tag2      = (new VideoTags())->get($file2Info['video_key'], $sfile);
        $f2        = new FileReader($file2Info);
        $genre2Dir = $f2->getGenre();

        // utmdump([$tag1['genre'],$genre1Dir]);
        // utmdump([$tag2['genre'],$genre2Dir]);
        if (str_contains($tag1['genre'], $genre1Dir)) {
            return [$file, $sfile];
        } elseif (str_contains($tag2['genre'], $genre2Dir)) {
            return [$sfile, $file];
        }

        // utmdd([$file, $file1Key, $tag1, $video1Info, $genreDir]);

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
        // $cacheKey  = md5($file.'_vinfo_cache');
        // $videoInfo = MediaCache::get($cacheKey);

        // if (false === $videoInfo) {
        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();
        $audios             = $mediaInfoContainer->getAudios();
        //
        // $videoInfo['file']     = $file;
        $videoInfo['filesize'] = filesize($file);
        foreach ($audios as $audio) {
            $videoInfo['codec_type'] = (string) $audio->get('kind_of_stream');
        }
        foreach ($videos as $video) {
            $videoInfo['format'] = (string) $general->get('format');
            if ('JPEG' == $video->get('format')->getshortname()) {
                continue;
            }

            $bit_rate = $video->get('bit_rate');
            if (null === $bit_rate) {
                $bit_rate = $video->get('maximum_bit_rate');
            }

            $videoInfo['frame_count'] = (string) $video->get('frame_count');
            $videoInfo['bit_rate']    = (string) $bit_rate->getAbsoluteValue();
            $videoInfo['width']       = (string) $video->get('width')->getAbsoluteValue();
            $videoInfo['height']      = (string) $video->get('height')->getAbsoluteValue();
            $videoInfo['duration']    = $video->get('duration')->getMilliseconds();
        }

        // MediaCache::put($cacheKey, $videoInfo);
        // }
        return $videoInfo;
    }
}
