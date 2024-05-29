<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const MYLIFEINMIAMI_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}p|k.mp4/i';

class MyLifeInMiami extends Patterns
{
    public $regex = [
        'mylifeinmiami' => [
            'artist' => [
                'pattern'             => MYLIFEINMIAMI_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],

            'title'  => [
                'pattern' => MYLIFEINMIAMI_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
            'studio' => [
                //     'pattern' => '/^([a-zA-Z]+)_.*.mp4/i',
            ],
        ],
    ];
}
