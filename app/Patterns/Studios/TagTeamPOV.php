<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

const TAGTEAMPOV_REGEX_COMMON = '//i';

class TagTeamPOV extends Patterns
{
    public $studio = 'Tag Team POV';

    public $regex = [
        'tagteampov' => [

            'title' => [
                'pattern' => '/([_a-zA-Z0-9-]+)_[0-9kpPHD]{1,5}.mp4/i',
                'delim'   => '-',
                'match'   => 1,
            ],
        ],
    ];
}
