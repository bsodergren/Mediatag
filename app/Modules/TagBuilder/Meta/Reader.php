<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\Meta;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\ReadExec;
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
        
        $this->expandArray($videoData);
        $this->tag_array = $this->getvideoData($videoData);
       // utmdd([__METHOD__,$this->tag_array,$videoData]);

    }

    public function __call($method, $arguments)
    {
        $this->get($method);
    }

    public function getvideoData(array $file_array)
    {
        $read = new ReadExec($file_array, Mediatag::$input, Mediatag::$output);       
        $video_info = $read->read();
        return $video_info[$this->video_key]['metatags'];
    }

    private function get($tag)
    {
        if (!\array_key_exists($tag, $this->tag_array)) {
            $this->tag_array[$tag] = null;
        }

        return $this->tag_array[$tag];
    }
}
