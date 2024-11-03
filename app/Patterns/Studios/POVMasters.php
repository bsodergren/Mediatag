<?php
/**
 * Command like Metatag writer for video files.
 *
 */

namespace Mediatag\Patterns\Studios;
use Mediatag\Modules\TagBuilder\Patterns;


use Mediatag\Patterns\Studios\AdultTime;

const POVMASTERS_REGEX_COMMON = '//i';

class POVMasters extends AdultTime
{

    public $studio = 'POV Masters';
    public $network = 'Adult Time';

}