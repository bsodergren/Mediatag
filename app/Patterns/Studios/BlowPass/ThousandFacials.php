<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\BlowPass;

use Mediatag\Core\Mediatag;
use Mediatag\Patterns\Studios\BlowPass;

const THOUSANDFACIALS_REGEX_COMMON = '/([a-zA-Z0-9\-]+)\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,5}.mp4/i';

class ThousandFacials extends BlowPass
{
    public $studio = '1000 Facials';
    

}
