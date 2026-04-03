<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks\traits;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Filesystem\MediaFile;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

trait ProcessCallbacks
{
    use CallbackCommon;

    public $currentProgress = null;

    public function ReadMetaOutput($type, $buffer)
    {
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/metadata/" . $this->video_key . ".log", $buffer . PHP_EOL);
        //  $buffer = MediatagExec::cleanBuffer($buffer);
        if ($type === Process::ERR) {
            $this->errors .= $buffer;
        } else {
            $this->getMetaValueFromBuffer($buffer);
        }
        $this->stdout .= $buffer;
    }

    public function WriteMetaOutput($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        // MediaFile::file_append_file(__LOGFILE_DIR__ . '/metadata_' . $this->video_key . '.log', $buffer . PHP_EOL);

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
                // utmdump($buffer);
                //  $buffer = trim($buffer) . "\n";

                if (Option::isFalse('no-progress')) {
                    $progressArray = explode('|', $buffer);
                    array_pop($progressArray);
                    foreach ($progressArray as $k => $v) {
                        $output = trim($v);
                        preg_match('/([0-9]+%)/', $output, $perc);
                        if (isset($perc[1]) &&
                        $this->currentProgress !== $perc[1]) {
                            // MediaFile::file_append_file(__LOGFILE_DIR__ . '/buffer/' . $this->video_key . '.log', $output . PHP_EOL);

                            $this->Display->processOutput->overwrite($output);
                            $this->currentProgress = $perc[1];
                        }
                    }
                } else {
                    preg_match('/([0-9]+%)/', $buffer, $output_array);

                    // $this->progressIndicator->advance();
                    $this->Display->processOutput->overwrite("\t" . $output_array[1]);
                }
            } else {
                $this->Display->processOutput->overwrite($buffer);
            }
        }
    }
}
