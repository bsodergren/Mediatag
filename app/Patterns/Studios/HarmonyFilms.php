<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\AdultTime;

const HARMONYFILMS_REGEX_COMMON = '//i';

class HarmonyFilms extends AdultTime
{
    public $studio  = 'Harmony Films';
    public $network = 'Adult Time';

}
