<?php

/**
 * Command like Metatag writer for video files.
 */

namespace  Mediatag\Patterns\Studios\AdultTime ;

const MODERNDAYSINS_REGEX_COMMON = '//i';


use Mediatag\Patterns\Studios\AdultTime\AdultTime;
class  ModernDaySins  extends  AdultTime
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
