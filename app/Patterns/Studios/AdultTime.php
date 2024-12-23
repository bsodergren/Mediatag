<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const ADULTTIME_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i';

class AdultTime extends Patterns
{
    public $studio = 'Adult Time';

    public $network = 'Adult Time';

    public $regex = [
        'adulttime' => [
            'artist' => [
                'pattern'             => ADULTTIME_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => true,
            ],

            'title'  => [
                'pattern' => ADULTTIME_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
            // 'studio' => [
            //     //     'pattern' => '/^([a-zA-Z]+)_.*.mp4/i',
            // ],
        ],
    ];
}
