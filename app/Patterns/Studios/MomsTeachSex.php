<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const MOMSTEACHSEX_REGEX_COMMON = '//i';

class MomsTeachSex extends Nubiles
{
    public $studio = 'Moms Teach Sex';

    public $regex = [
        'momsteachsex' => [
            'title' => [
                'pattern' => '/(momsteachsex_)?([a-zA-Z_\-0-9]{1,})\_[0-9]{0,10}/i',
                // 'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];
}
