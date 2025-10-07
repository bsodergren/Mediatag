<?php
namespace Mediatag\Commands\Update\Commands\NewF;

use Mediatag\Modules\Executable\MediatagExec;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\ShellExec;

/**
 * Command like Metatag writer for video files.
 */
trait Helper
{
    public function dbUpdate()
    {
        $MediaExec = new ShellExec();
        $MediaExec->mediaDb(false);
        return true;
    }
}
