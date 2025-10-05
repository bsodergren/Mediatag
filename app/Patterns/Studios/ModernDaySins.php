<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const MODERNDAYSINS_REGEX_COMMON = '//i';

class ModernDaySins extends AdultTime
{
    public $studio = 'Modern Day Sins';

    public $network = 'Adult Time';

    public $regex = [
        'moderndaysins' => [
            'artist' => [
                'pattern'             => ADULTTIME_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],

            'title'  => [
                'pattern' => ADULTTIME_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
        ],
    ];
}
