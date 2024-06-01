<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const DDFNETWORK_REGEX_COMMON = '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i';

class DdfNetwork extends Patterns
{
    public $regex        = [
        'ddfnetwork' => [
            'artist' => [
                'pattern'             => DDFNETWORK_REGEX_COMMON,
                'delim'               => '_and_',
                'match'               => 1,
                'artistFirstNameOnly' => true,
            ],
            'studio' => [
                'pattern' => '/.*\_-\_((.*))(\-[0-9]{3,5}?)\.mp4/i',
            ],
            'title'  => [
                'pattern' => DDFNETWORK_REGEX_COMMON,
                'match'   => 2,
                'delim'   => '_',
            ],
        ],
    ];

    public $artist_match = [
        'chloe' => 'chloe something',
        'chloé' => 'chloe something',
    ];

    public function __construct($object)
    {
        parent::__construct($object);
    }
}
