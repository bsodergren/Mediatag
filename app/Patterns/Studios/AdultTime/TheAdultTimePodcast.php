<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\AdultTime;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\AdultTime\AdultTime;

const THEADULTTIMEPODCAST_REGEX_COMMON = '//i';

class TheAdultTimePodcast extends AdultTime
{
    public $studio = 'The Adult Time Podcast';

    public $network = 'Adult Time';
}
