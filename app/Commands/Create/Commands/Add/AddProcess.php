<?php

namespace Mediatag\Commands\Create\Commands\Add;

/**
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Create\Commands\Add\AddHelper;
use Mediatag\Commands\Create\Process;
use Mediatag\Core\Mediatag;

class AddProcess extends Process
{
    use AddHelper;
}
