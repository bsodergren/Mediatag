<?php

namespace Mediatag\Commands\Create\Commands\Add;

/**
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Create\Commands\Add\AddHelper;
use Mediatag\Commands\Create\Process;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Utilities\Chooser;
use UTM\Utilities\Option;

class AddProcess extends Process
{
    use AddHelper;

    public function __construct($input, $output)
    {
        parent::__construct($input, $output);
    }
}
