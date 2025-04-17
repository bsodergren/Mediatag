<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section;

use Mediatag\Utilities\Strings;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Symfony\Component\Filesystem\Filesystem;

class Gallery extends VideoInfo
{
    public $VideoDataTable = __MYSQL_VIDEO_METADATA__;
    public $video_name;

    public $actionText = '<comment>Updated Gallery Info</comment>';

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        $parts = pathinfo($this->video_file);

        $vdata = [
            'video_file' => $this->video_file,
            'video_path' => $parts['dirname'],
            'video_name' => $parts['basename'],
            'video_key'  => $this->video_key,
        ];

        $studio_dir = (new Filesystem())->makePathRelative($vdata['video_path'], __PLEX_HOME__.'/'.__LIBRARY__);
        $studio_dir = trim($studio_dir, '/');
        $arr        = explode('/', $studio_dir);
        if (\array_key_exists(0, $arr)) {
            $tagList['network'] = Strings::clean($arr[0]);
        }
        if (\array_key_exists(1, $arr)) {
            $tagList['studio'] = Strings::clean($arr[1]);
        }
        if (\array_key_exists(2, $arr)) {
            $tagList['genre'] = Strings::clean($arr[2]);
        }

        // if (\array_key_exists('title', $tagList)) {
        //     $tagList['title'] = Strings::clean($tagList['title']);
        // }

        if (!\array_key_exists('studio', $tagList)) {
            $tagList['studio'] = $tagList['network'];
            unset($tagList['network']);
        }
        //     if (str_contains($tagList['studio'], '/')) {
        //         $studioArr               = explode('/', $tagList['studio']);
        //         $tagList['studio']       = $studioArr[0];
        //         // $tagList['network'] = $studioArr[1];
        //     }
        // }

        // $tagList['subLibrary'] = StorageDB::getSubLibrary($vdata['video_path']);
        // utmdd([$tagList,$studio_dir,$arr]);

        $this->tagList = $tagList;

        return $tagList;
    }
}
