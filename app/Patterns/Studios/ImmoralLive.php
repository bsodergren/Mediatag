<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;

use Mediatag\Modules\TagBuilder\Patterns;

use Mediatag\Patterns\Studios\Blowpass;

const IMMORALLIVE_REGEX_COMMON = '//i';

class ImmoralLive extends Blowpass
{
    public $subStudio = 'Immoral Live';

}
