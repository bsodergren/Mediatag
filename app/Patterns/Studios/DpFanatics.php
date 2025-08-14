<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const DPFANATICS_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i';

class DpFanatics extends TwentyFirstSextury
{
    public $studio = 'Dp Fanatics';

    public $regex = [
        'dpfanatics' => [
            'artist' => [
                'pattern'             => DPFANATICS_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z]{1,5})_.*/i',
            ],

            'title'  => [
                'pattern' => DPFANATICS_REGEX_COMMON,
                'delim'   => '_',
                'match'   => 1,
            ],
        ],
    ];
}
