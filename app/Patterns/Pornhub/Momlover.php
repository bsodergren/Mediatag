<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Pornhub;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaCache;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Pornhubdb\Helpers\CVSUtils;
use UTM\Utilities\Debug\Timer;

class Momlover extends Patterns
{
   
    public function getFilename($file)
    {

        $filename = basename($file);
        $filepath = dirname($file);
        $matched = preg_match('/(.+)(S[0-9]{1,2}).*(E[0-9]{1,2})(.*)(-p?h?[a-f0-9]{4,}).*(\.mp4)/', $filename, $matches);
        // utmdd(vars: [$matched, $matches]);
        if(count($matches) == 0 )
        {

           $Secmatched = preg_match('/(S[0-9]{1,2}-E[0-9]{1,2})_?_?(.*)(_|-)(p?h?[a-f0-9]{4,}\.mp4)/',
            $filename, $SecMatch);
        // utmdd([$Secmatched, $SecMatch]);

        if(count($SecMatch) == 0 )
        {
                return $file;
           }
           $episode = $SecMatch[1];
           $filebodyName = trim($SecMatch[2],'_');
           $videoKey = $SecMatch[4];
           $fileExt = '';
        } else {

            $episode = $matches[2] . "-" . $matches[3];
            $filebodyName = $matches[1];
            $videoKey = str_replace("-","",$matches[5]);
            $fileExt = $matches[6];
        }

        $filebody = str_replace("_-_","",$filebodyName);
        $filebody = str_replace("_-","",$filebody);
        $newFilename = $episode."_".$filebody.'-'.$videoKey.$fileExt;
        // utmdd($newFilename);
        return $filepath . DIRECTORY_SEPARATOR . $newFilename;

    }
}
