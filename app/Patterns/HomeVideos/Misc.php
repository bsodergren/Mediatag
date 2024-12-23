<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\HomeVideos;
use Mediatag\Modules\TagBuilder\Patterns;

 

const MISC_REGEX_COMMON = '//i';

class Misc extends Patterns
{

    public $studio = 'Misc';
    public function getFilename($file)
    {
        // utminfo(func_get_args());
        $filename = basename($file);
        $path     = str_replace('/'.$filename, '', $file);
        $name     = strtolower($filename);
        $name     = ucfirst($name);
        if ($filename == $name) {
            return $file;
        }
        $newFile = $path.\DIRECTORY_SEPARATOR.$name;
            //   utmdd($newFile);

        return $newFile;
    }

}