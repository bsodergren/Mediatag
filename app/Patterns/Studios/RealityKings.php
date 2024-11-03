<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\Patterns;

class RealityKings extends Patterns
{
    public $studio          = 'Reality Kings';

    public $regex           = [
        'realitykings' => [
            'studio' => [
                'pattern' => '/^([a-zA-Z]{1,5})_.*/i',
            ],
            'title'  => [
                'pattern' => '/^([a-zA-Z]{1,5})_([_a-zA-Z0-9]+).mp4/i',
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];

    public $replace_studios = [
        'ES'  => 'Euro Sluts',
        'BN'  => 'Big Naturals',
        'MBT' => 'Moms Bang Teens',
        'MC'  => 'Monster Cock',
    ];
}
