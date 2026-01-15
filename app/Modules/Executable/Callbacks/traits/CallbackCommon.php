<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks\traits;

use const PHP_EOL;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use function array_key_exists;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Filesystem\MediaFile;

use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Executable\Helper\VideoDownloader;

/**
 * Command like Metatag writer for video files.
 */
trait CallbackCommon
{
    public function splitFileOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        Mediatag::$output->writeln($buffer);
        // echo $buffer;
        // $this->Console->writeln($buffer);
    }

    public function LogOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        // Mediatag::$output->writeln($buffer);
        $opt     = Option::getOptions();
        $command = null;
        if (array_key_exists('command', $opt)) {
            $command = '_' . $opt['command'];
        }
        $this->progress->advance();
        // MediaFile::file_append_file(__LOGFILE_DIR__ . '/buffer/log_' . __SCRIPT_NAME__ . $command . '.log', $buffer . PHP_EOL);
    }

    public function Output($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        Mediatag::$output->writeln($buffer);

        // echo $buffer;
    }

    public function ReadOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        $this->stdout .= $buffer;
    }

    public function ProcessOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        if ($type === Process::ERR) {
            // echo 'ERR > ' . $buffer;
        } else {
            // echo 'OUT > ' . $buffer;
        }
    }
}
