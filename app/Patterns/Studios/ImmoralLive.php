<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

const IMMORALLIVE_REGEX_COMMON = '//i';

class ImmoralLive extends BlowPass
{
    public $studio = 'Immoral Live';

    public function __construct($object)
    {
        parent::boot($this);
        parent::__construct($object);
    }
}
