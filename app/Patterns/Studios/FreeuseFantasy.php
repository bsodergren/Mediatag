<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\Studios;
use Mediatag\Modules\TagBuilder\Patterns;

use Mediatag\Patterns\Studios\TeamSkeet;

const FREEUSEFANTASY_REGEX_COMMON = '//i';

class FreeuseFantasy extends TeamSkeet
{

    public $subStudio = 'Freeuse Fantasy';
    public $regex     = [
        'pervmom' => [
            'artist' => [
                'pattern'             => '/[a-z]{1,}\_([a-zA-Z_]{1,})[0-9]?\_full.*[0-9]{1,4}.*\.mp4/i',
                'delim'               => '_and_',
                'match'               => 1,
                'artistFirstNameOnly' => false,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z]+)_.*/i', ],
        ],
    ];
}