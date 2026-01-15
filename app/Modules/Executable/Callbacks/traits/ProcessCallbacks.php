<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks\traits;

/*
 * Command like Metatag writer for video files.
 */

use UTM\Utilities\Option;
use UTM\Bundle\Monolog\UTMLog;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Executable\MediatagExec;

trait ProcessCallbacks
{
    use CallbackCommon;

    public function ReadMetaOutput($type, $buffer)
    {
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/metadata/" . $this->video_key . ".log", $buffer . PHP_EOL);
        //  $buffer = MediatagExec::cleanBuffer($buffer);
        if ($type === Process::ERR) {
            $this->errors .= $buffer;
        } else {
            $this->getMetaValue($buffer);
        }
        $this->stdout .= $buffer;
    }

    public function WriteMetaOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/metadata_" . $this->video_key . ".log", $buffer . PHP_EOL);

        if ($type === Process::ERR) {
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
            } elseif (str_contains($buffer, 'Progress')) {
                if (Option::isFalse('no-progress')) {
                    $this->Display->processOutput->overwrite($buffer);
                }
            } else {

                $this->Display->processOutput->overwrite($buffer);
            }
        }
    }
}
