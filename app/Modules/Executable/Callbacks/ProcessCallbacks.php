<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Modules\Filesystem\MediaFile;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;

trait ProcessCallbacks
{
    use CallbackCommon;

    public function ReadMetaOutput($type, $buffer)
    {
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/metadata/" . $this->video_key . ".log", $buffer . PHP_EOL);
        //  $buffer = $this->cleanBuffer($buffer);
        if (Process::ERR === $type) {
            $this->errors .= $buffer;
        } else {
            $this->getMetaValue($buffer);
        }
        $this->stdout .= $buffer;
    }

    public function WriteMetaOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        if (Process::ERR === $type) {
            $this->errors .= $buffer;
        // UTMlog::logError('Writing Metadata', $buffer);
        // UTMlog::logError('Writing Metadata', $this->video_file);
        } else {
            if (str_contains($buffer, 'error')) {
                $this->errors .= $buffer;
            // UTMlog::logError('Writing Metadata', $buffer);
            // UTMlog::logError('Writing Metadata', $this->video_file);
            } elseif (str_contains($buffer, 'warning')) {
                // UTMlog::logWarning('Writing Metadata', $buffer);
                // UTMlog::logWarning('Writing Metadata', $this->video_file);
            } else {
                $this->Display->processOutput->overwrite($buffer);
            }
        }
    }
}
