<?php

namespace Mediatag\Commands\Update\Commands\NewF;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Executable\ShellExec;
use UTM\Utilities\Option;

/**
 * Command like Metatag writer for video files.
 */
trait Helper
{
    public function dbUpdate()
    {
        $MediaExec = new ShellExec;
        $MediaExec->mediaDb(false);

        return true;
    }
}
