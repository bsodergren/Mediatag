<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class Bang extends Patterns
{
    public $studio = 'Bang';

    public $regex = [
        'bang' => [
            'artist' => [
                'pattern'             => '/(glamkore|pretty_and_raw|rammed|trickery)\_([a-zA-Z_]{1,})[0-9]?\_scene.*[0-9]{1,4}.*\.mp4/i',
                'delim'               => '_and_',
                'match'               => 2,
                'artistFirstNameOnly' => false,
            ],
            // 'studio' => [
            //     'pattern' => '/^([a-zA-Z]+)-.*/i', ],
        ],
    ];

    public $replace_studios = [
        'glamkore' => 'Glamkore',
        'pretty'   => 'Pretty and Raw',
        'rammed'   => 'Rammed',
        'trickery' => 'Trickery',
    ];

    // public function __construct($object)
    // {
    //     // utminfo(func_get_args());

    //     parent::__construct($object);
    //     parent::$StudioKey = $this->studio;
    // }
}
