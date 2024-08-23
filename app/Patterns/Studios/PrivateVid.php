<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\Patterns;

class PrivateVid extends Patterns
{
    public $regex = [
        'privatevid' => [
            'artist' => [
                'pattern'             => '/([a-zA-Z0-9\- ]+)\_[a-z]{2,4}[0-9]{2,8}\_.*/i',
                'delim'               => ' and ',
                'match'               => 1,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i',
                'match'   => 2,
                'delim'   => '_',
            ],
            'studio' => [
                'pattern' => false,
            ],
        ],
    ];
}
