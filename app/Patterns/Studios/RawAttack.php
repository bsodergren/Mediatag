<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class RawAttack extends Patterns
{
    public $studio = 'Raw Attack';
    public $regex  = [
        'rawattack' => [
            'artist' => [
                'pattern'             => '/[a-z-]{1,}\_([a-zA-Z_]{1,})[0-9]?\_full.*[0-9]{1,4}.*\.mp4/i',
                'delim'               => '_and_',
                'match'               => 1,
                'artistFirstNameOnly' => false,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z-]+)_.*/i', ],

            'title'  => [
                'pattern' => '/^([a-zA-Z-]+)_.*/i',
                'match'   => 1,
                // 'delim'   => '_',
            ],
        ],
    ];
}
