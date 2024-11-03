<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\TeamSkeet;

const THELOFT_REGEX_COMMON = '//i';

class TheLoft extends TeamSkeet
{
    public $studio           = 'The Loft';
    public $network          = 'Team Skeet';

    public $regex            = [
        'theloft' => [
            'artist' => [
                'pattern'             => '/[a-z-]{1,}\_([a-zA-Z_]{1,})[0-9]?\_full.*[0-9]{1,4}.*\.mp4/i',
                'delim'               => '_and_',
                'match'               => 1,
                'artistFirstNameOnly' => false,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z-]+)_.*/i', ],
        ],
    ];
}
