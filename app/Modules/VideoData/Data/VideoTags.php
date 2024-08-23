<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Mediatag\Utilities\Strings;

class VideoTags extends VideoData
{
    public $VideoDataTable = __MYSQL_VIDEO_METADATA__;

    private $actionText    = "<comment>Updated Meta Tags</comment>";


    public function getText()
    {
        utminfo();



        return $this->actionText;

    }
    public function get($key, $file)
    {
        utminfo();

        $parts                 = pathinfo($this->video_file);

        $vdata                 = [
            'video_file' => $this->video_file,
            'video_path' => $parts['dirname'],
            'video_name' => $parts['basename'],
            'video_key'  => $this->video_key,
        ];

        $meta                  = new metaReader($vdata);
        // unset($tagList);
        $tagList               = $meta->getTagArray();

        if (\array_key_exists('title', $tagList)) {
            $tagList['title'] = Strings::clean($tagList['title']);
        }

        if (\array_key_exists('studio', $tagList)) {
            if (str_contains($tagList['studio'], '/')) {
                $studioArr            = explode('/', $tagList['studio']);
                $tagList['studio']    = $studioArr[0];
                $tagList['substudio'] = $studioArr[1];
            }
        }

        $tagList['subLibrary'] = StorageDB::getSubLibrary($vdata['video_path']);

        $this->tagList         = $tagList;
        return $tagList;
    }
}
