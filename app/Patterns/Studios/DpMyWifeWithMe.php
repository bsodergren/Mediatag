<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const DPMYWIFE_REGEX_COMMON = '/([a-z\-]+)-?([0-9]{1,2})?-scene-([0-9]+)_?(.*)?\_[0-9pk]{1,5}.mp4/i';

class DpMyWifeWithMe extends MileHighMedia
{
    // public $studio    = 'Reality Junkies';

    public $subStudio = 'DP My Wife With Me';

    public $regex     = [
        'dpmywifewithme' => [
            'artist' => [
                'pattern'             => DPMYWIFE_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 4,
                'artistFirstNameOnly' => true,
            ],
            'title'  => [
                'pattern' => DPMYWIFE_REGEX_COMMON,
                'delim'   => '-',
                'match'   => 1,
            ],
        ],
    ];
}
