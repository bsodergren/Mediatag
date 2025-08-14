<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const MARISKAX_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i';

class MariskaX extends Patterns
{
    public $studio = 'Mariska X';
    public $regex  = [
        'mariskax' => [
            'artist' => [
                'pattern'             => MARISKAX_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],

            'title'  => [
                'pattern' => MARISKAX_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
            'studio' => [
                //     'pattern' => '/^([a-zA-Z]+)_.*.mp4/i',
            ],
        ],
    ];
}
