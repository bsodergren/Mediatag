<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;

use Mediatag\Modules\TagBuilder\Patterns;

use Mediatag\Patterns\Studios\Blowpass;

const THROATED_REGEX_COMMON = '//i';

class Throated extends Blowpass
{
    public $subStudio = 'Throated';

}
