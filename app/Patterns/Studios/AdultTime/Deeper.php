<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\AdultTime;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\AdultTime\AdultTime;

const DEEPER_REGEX_COMMON = '//i';

class Deeper extends AdultTime
{
    public $studio = 'Deeper';

    public $network = 'Adult Time';
}
