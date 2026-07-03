<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\MomLover;

const MOMWANTSCREAMPIE_REGEX_COMMON = '//i';

use Mediatag\Patterns\Studios\MomLover\MomLover;

class MomWantsCreampie extends MomLover
{
    public $studio = 'Mom Wants Creampie';

    public $network = 'Mom Lover';

    public $regex = [
        'momwantscreampie' => [
            'title' => [
                'pattern' => '/([a-zA-Z]+)_([a-zA-Z_]{1,})\_[0-9]{0,10}/i',
                // 'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];
}
