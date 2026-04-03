<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const MOMWANTSTOBREED_REGEX_COMMON = '//i';

class MomWantsToBreed extends MomLover
{
    public $studio = 'Mom Wants To Breed';

    public $network = 'Mom Lover';

    public $regex = [
        'momwantstobreed' => [

            'title' => [
                'pattern' => '/([a-zA-Z]+)_([a-zA-Z_]{1,})\_[0-9]{0,10}/i',
                //'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];
}
