<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\DB;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\TagReader;

class Reader extends TagReader
{
    public $input;

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
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->expandArray($videoData);
        $this->tag_array = $this->getvideoData($videoData);
    }

    public function __call($method, $arguments)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->get($method);
    }

    public function getvideoData(array $file_array)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $video_info = $this->getVideoInfo($file_array['video_key']);
        if (null === $video_info) {
            return null;
        }

        return $video_info[$this->video_key]['metatags'];
    }

    private function get($tag)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (\array_key_exists($tag, $this->tag_array)) {
            if ('studio' == $tag) {
                if (\array_key_exists('substudio', $this->tag_array)) {
                    $this->tag_array[$tag] .= '/' . $this->tag_array['substudio'];
                }
            }

            return $this->tag_array[$tag];
        }

        return null;
    }

    private function getVideoInfo($key)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $query      = 'SELECT * FROM ' . __MYSQL_VIDEO_CUSTOM__ . " WHERE  video_key = '" . $key . "'";

        $result     = Mediatag::$dbconn->query($query);
        $info       = null;
        if (1 != \count($result)) {
            return null;
        }

        $tagArray   = __META_TAGS__;
        $tagArray[] = 'substudio';

        foreach ($tagArray as $tag) {
            if (null !== $result[0][$tag]) {
                $info[$key]['metatags'][$tag] = $result[0][$tag];
            }
        }

        return $info;
    }
}
