<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\DB;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\TagReader;

use function array_key_exists;
use function count;

class Reader extends TagReader
{
    public $input;

    public $videoData;

    public $output;

    public $video_file;

    public $video_name;

    public $video_key;

    public $video_path;

    public $video_info;

    public $db;

    public $tag_array;

    public $VideoInfo;

    public function __construct($videoData)
    {
        // utminfo(func_get_args());

        // $this->videoData = $videoData;
        $this->expandArray($videoData);
        $this->tag_array = $this->getvideoData($videoData);
    }

    public function __call($method, $arguments)
    {
        // utminfo(func_get_args());

        $this->get($method);
    }

    public function getvideoData(array $file_array)
    {
        // utminfo(func_get_args());

        $video_info = $this->getVideoInfo($file_array['video_key']);
        if ($video_info === null) {
            return null;
        }

        return $video_info[$this->video_key]['metatags'];
    }

    private function get($tag)
    {
        // utminfo(func_get_args());
        // Mediatag::notice('Getting tag info for {tag}', ['tag'=>$tag]);
        if (array_key_exists($tag, $this->tag_array)) {
            if ($tag == 'studio') {
                if (array_key_exists('network', $this->tag_array)) {
                    $this->tag_array[$tag] .= '/' . $this->tag_array['network'];
                }
            }

            $value = $this->tag_array[$tag];

            return $value;
        }

        return null;
    }

    private function getVideoInfo($key)
    {
        // utminfo(func_get_args());

        $query = "SELECT m.title as title ,
  m.artist as artist ,
  m.genre as genre ,
  m.studio as studio ,
  m.network as network ,
  m.keyword as keyword
  FROM  mediatag_video_metadata m WHERE m.video_key = '" . $key . "'";

        // $query      = 'SELECT * FROM ' . __MYSQL_VIDEO_CUSTOM__ . " WHERE  video_key = '" . $key . "'";
        // utmdd($query);

        $result = Mediatag::$dbconn->query($query);
        $info   = null;
        if (count($result) != 1) {
            return null;
        }

        $tagArray   = __META_TAGS__;
        $tagArray[] = 'network';

        foreach ($tagArray as $tag) {
            if ($result[0][$tag] !== null) {
                $info[$key]['metatags'][$tag] = $result[0][$tag];
            }
        }

        return $info;
    }
}
