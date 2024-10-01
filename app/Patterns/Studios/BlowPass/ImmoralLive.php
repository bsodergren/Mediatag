<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\BlowPass;

use Mediatag\Core\Mediatag;

use Mediatag\Modules\TagBuilder\Patterns;

use Mediatag\Patterns\Studios\BlowPass;

const IMMORALLIVE_REGEX_COMMON = '//i';

class ImmoralLive extends BlowPass
{
     public $studio = 'Immoral Live';

    public function __construct($object){
        parent::boot($this);
        parent::__construct($object);
    }

}
