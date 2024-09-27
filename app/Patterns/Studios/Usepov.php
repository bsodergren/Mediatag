<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\Studios;
use Mediatag\Modules\TagBuilder\Patterns;

use Mediatag\Patterns\Studios\TeamSkeet;

const USEPOV_REGEX_COMMON = '//i';

class Usepov extends TeamSkeet
{

    public $studio = 'Use POV';

}