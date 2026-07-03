<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\NaughtyAmerica;

use Mediatag\Modules\TagBuilder\Patterns;

const NAUGHTYOFFICE_REGEX_COMMON = '//i';

use Mediatag\Patterns\Studios\NaughtyAmerica\NaughtyAmerica;

class NaughtyOffice extends NaughtyAmerica
{
    public $network = 'Naughty America';

    public $studio = 'Naughty Office';
}
