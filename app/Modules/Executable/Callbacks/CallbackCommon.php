<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Symfony\Component\Process\Process;
use UTM\Utilities\Option;

use function array_key_exists;

use const PHP_EOL;

/**
 * Command like Metatag writer for video files.
 */
trait CallbackCommon
{
    public function cleanBuffer($buffer)
    {
        $buffer = str_replace(["\n", "\r"], '', $buffer);

        return $buffer;
    }

    public function splitFileOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        Mediatag::$output->writeln($buffer);
        // echo $buffer;
        // $this->Console->writeln($buffer);
    }

    public function LogOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        // Mediatag::$output->writeln($buffer);
        $opt     = Option::getOptions();
        $command = null;
        if (array_key_exists('command', $opt)) {
            $command = '_'.$opt['command'];
        }
        $this->progress->advance();
        MediaFile::file_append_file(__LOGFILE_DIR__.'/buffer/log_'.__SCRIPT_NAME__.$command.'.log', $buffer.PHP_EOL);
    }

    public function Output($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        $this->Console->writeln($buffer);

        // echo $buffer;
    }

    public function ReadOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        $this->stdout .= $buffer;
    }

    public function ProcessOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        if (Process::ERR === $type) {
            // echo 'ERR > ' . $buffer;
        } else {
            // echo 'OUT > ' . $buffer;
        }
    }
}
