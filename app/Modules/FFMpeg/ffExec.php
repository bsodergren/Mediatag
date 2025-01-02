<?php
namespace Mediatag\Modules\FFMpeg;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Filesystem\MediaFile;
use Mhor\MediaInfo\MediaInfo;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;
trait  FfExec
{

    public function FrameCountCallback($type, $buffer)
    {
    }

    public function ProgressbarOutput($type, $buffer)
    {
        $outputText = '';
        $buffer     = str_replace("\n", '', $buffer);
        // utmdd($buffer);
        $this->progress->advance();
        switch ($buffer) {
            case str_starts_with($buffer, 'frame='):
                $outputText = $buffer;
                // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/ffmpeg_convert.log", $buffer . PHP_EOL);
                $this->progress->advance();

                break;
        }
        if ('' != $outputText) {
            // Mediatag::$output->write($outputText);
        }
    }

    public function Outputdebug($type, $buffer)
    {
        // $buffer = str_replace("\n", '', $buffer);
        // utmdd($buffer);
        $this->progress->advance();
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/ffmpeg.log", $buffer . PHP_EOL);
        // Mediatag::$output->writeln($buffer);
        //  Mediatag::$output->writeln($this->cmdline);

        if (Process::ERR === $type) {
            // echo 'ERR > '.$buffer;
        }
    }

    private function ffExec($exec, $cmdOptions, $callback)
    {
        // $this->ffmpeg = [CONFIG['FFMPEG_CMD']];

        $command = array_merge([$exec], $this->ffmpegArgs, $cmdOptions);

        $process = new Process($command);
        $process->setTimeout(null);
        // $process->start($callback);
        $process->start();
        $process->wait($callback);
        
        //  $process->start();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

}