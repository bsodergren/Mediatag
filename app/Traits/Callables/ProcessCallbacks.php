<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Callables;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

trait ProcessCallbacks
{
    use CallableHelper;

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
        if (\array_key_exists('command', $opt)) {
            $command = '_'.$opt['command'];
        }
        $this->progress->advance();
        MediaFile::file_append_file(__LOGFILE_DIR__.'/buffer/log_'.__SCRIPT_NAME__.$command.'.log', $buffer.\PHP_EOL);
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

    public function ProcessOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        if (Process::ERR === $type) {
            // echo 'ERR > ' . $buffer;
        } else {
            // echo 'OUT > ' . $buffer;
        }
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

    public function watchlistCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        if (str_contains($buffer, '[PLAYLIST]')) {
            $this->Console->writeln($buffer);
        // if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):?\s?+(.*)?/', $buffer, $matches)) {
        //     if (\array_key_exists(2, $matches)) {
        //         if ('' != $matches[2]) {
        //             $outputText                   = '  <id> '.$matches[2].' cancelled </id>';
        //             $this->Console->writeln($outputText);

        //         }
        //     }
        // }
        } else {
            $this->key = $buffer;
            $this->updatePlaylist($this->pltype);
        }
        // $this->Console->writeln($this->key );
    }

    public function downloadJsonCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        // $outputText = '';
        // $line_id    = \PHP_EOL . '<id>' . $this->num_of_lines . '</id>';
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/json" . ".log", $buffer . PHP_EOL);

        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (\array_key_exists(2, $matches)) {
                if ('' != $matches[2]) {
                    $this->key = $matches[2];
                }
            }
        }

        switch ($buffer) {
            case str_contains($buffer, '[info]'):
                if (str_contains($buffer, 'as JSON')) {
                    $this->yt_json_string = $buffer;
                }
                break;
        }
    }
}
