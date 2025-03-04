<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section;

use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Utilities\Strings;

class VideoTags extends VideoInfo
{
    public $VideoDataTable = __MYSQL_VIDEO_METADATA__;
    public $tagList;

    public $actionText = '<comment>Updated Meta Tags</comment>';

    public function get($video_key, $video_file)
    {
        // utminfo(func_get_args());

        $parts = pathinfo($video_file);

        $vdata = [
            'video_file' => $video_file,
            'video_path' => $parts['dirname'],
            'video_name' => $parts['basename'],
            'video_key'  => $video_key,
        ];

        $meta = new metaReader($vdata);
        // unset($tagList);

        $tagList = $meta->getTagArray();

        if (\array_key_exists('title', $tagList)) {
            $tagList['title'] = Strings::clean($tagList['title']);
        }

        if (\array_key_exists('studio', $tagList)) {
            if (str_contains($tagList['studio'], '/')) {
                $studioArr         = explode('/', $tagList['studio']);
                $tagList['studio'] = $studioArr[0];
                // $tagList['network'] = $studioArr[1];
            }
        }

        $tagList['subLibrary'] = StorageDB::getSubLibrary($vdata['video_path']);

        $this->tagList = $tagList;

        return $tagList;
    }
}
