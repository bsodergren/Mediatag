<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const TOUCHMYWIFE_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i';

class TouchMyWife extends AdultTime
{
    public $studio  = 'Touch My Wife';
    public $network = 'Adult Time';
    public $regex   = [
        'touchmywife' => [
            'artist' => [
                'pattern'             => TOUCHMYWIFE_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => true,
            ],

            'title'  => [
                'pattern' => TOUCHMYWIFE_REGEX_COMMON,
                'match'   => 1,
                'delim'   => '',
            ],
            // 'studio' => [
            //     //     'pattern' => '/^([a-zA-Z]+)_.*.mp4/i',
            // ],
        ],
    ];
}
