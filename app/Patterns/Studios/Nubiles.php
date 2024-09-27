<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class Nubiles extends Patterns
{
    public $studio = 'Nubiles';
    public $regex  = [
        'nubiles' => [


            'title'  => [
                'pattern' => '/([a-zA-Z]+)_([a-zA-Z_]{1,})\_[0-9]{0,10}/i',
                //'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];
}
