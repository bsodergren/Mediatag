<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class Nubiles extends Patterns
{
    public $studio  = 'Nubiles';
    public $network = 'Nubiles';
    public $regex   = [
        'nubiles' => [
            'artist' => [
                'pattern'             => '/([a-zA-Z]{1,})[_-]([sSeE_0-9]{1,})_([a-zA-Z_]{1,})_([0-9p]{0,5}_?.*)/i',
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => '/([a-zA-Z]{1,})[_-]([sSeE_0-9]{1,})_([a-zA-Z_]{1,})_([0-9p]{0,5}_?.*)/i',
                // 'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '_',
                'match'   => 1,
            ],
        ],
    ];
}
