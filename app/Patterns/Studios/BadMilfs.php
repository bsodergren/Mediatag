<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\TeamSkeet;

const BADMILFS_REGEX_COMMON = '//i';

class BadMilfs extends TeamSkeet
{
    public $studio = 'Bad Milfs';

}
