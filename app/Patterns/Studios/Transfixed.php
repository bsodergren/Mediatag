<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\Studios;
use Mediatag\Modules\TagBuilder\Patterns;


use Mediatag\Patterns\Studios\AdultTime;

const TRANSFIXED_REGEX_COMMON = '//i';

class Transfixed extends AdultTime
{

    public $studio = 'Transfixed';
    public $network = 'Adult Time';

}