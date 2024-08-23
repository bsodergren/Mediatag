<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;

class PervMom extends TeamSkeet
{
    // public $studio = 'Perv Mom';
    public $subStudio = 'Perv Mom';

    // public $subStudio = '';
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
