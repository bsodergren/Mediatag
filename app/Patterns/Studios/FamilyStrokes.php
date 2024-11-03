<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\TeamSkeet;

const FAMILYSTROKES_REGEX_COMMON = '//i';

class FamilyStrokes extends TeamSkeet
{
    public $studio = 'Family Strokes';
    // public $regex     = [
    //     'familystrokes' => [
    //         'artist' => [
    //             'pattern'             => '/[a-z]{1,}\_([a-zA-Z_]{1,})[0-9]?\_full.*[0-9]{1,4}.*\.mp4/i',
    //             'delim'               => '_and_',
    //             'match'               => 1,
    //             'artistFirstNameOnly' => false,
    //         ],
    //         'studio' => [
    //             'pattern' => '/^([a-zA-Z]+)_.*/i', ],
    //     ],
    // ];

}
