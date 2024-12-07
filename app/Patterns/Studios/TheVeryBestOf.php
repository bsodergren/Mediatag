<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\Studios;
use Mediatag\Modules\TagBuilder\Patterns;


use Mediatag\Patterns\Studios\TwentyFirstSextury;

const THEVERYBESTOF_REGEX_COMMON = '//i';

class TheVeryBestOf extends TwentyFirstSextury
{

    public $studio = 'The Very Best Of';
    public $network = '21st Sextury';

}