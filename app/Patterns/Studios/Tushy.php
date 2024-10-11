<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\Patterns;

const TUSHY_REGEX_COMMON = '/([a-zA-Z0-9_]+)(_s[0-9]{2,3}_|\-_)([a-zA-Z_]+)_[0-9pk]{2,6}/i';

class Tushy extends Patterns
{

public $studio = "Tushy";
    public $regex        = [
        'tushy' => [
            'artist' => [
                'pattern'             => TUSHY_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => TUSHY_REGEX_COMMON,
                'match'   => 1,
                'delim'   => '_',
            ],
            'studio' => [
                'pattern' => false,
            ],
        ],
    ];

    public $artist_match = [
        'keisha'  => 'Keisha Grey',
        'natasha' => 'Natasha Nice',
    ];
}
