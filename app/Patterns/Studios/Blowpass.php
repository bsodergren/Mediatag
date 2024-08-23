<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\Patterns;

const BLOWPASS_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i';

class Blowpass extends Patterns
{
    public $studio = 'Blow Pass';


    public $regex  = [
        'blowpass' => [
            'artist' => [
                'pattern'             => BLOWPASS_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],

            'title'  => [
                'pattern' => BLOWPASS_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
        ],
    ];

    // public static function customStudio($key_studio, $arr)
    // {
    //     if (\array_key_exists(1, $arr)) {
    //         if ('Blowpass' != $arr[0]) {
    //             if ($key_studio == $arr[1]) {
    //                 $tmp    = $arr[0];
    //                 $arr[0] = $arr[1];
    //                 $arr[1] = $tmp;
    //             }
    //         }
    //     }

    //     return $arr;
    // }
}
