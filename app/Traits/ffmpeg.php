<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Nette\Utils\Callback;
use Mediatag\Core\Mediatag;
use Nette\Utils\FileSystem;
use UTM\Bundle\Monolog\UTMLog;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Filesystem\MediaFile;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;

trait ffmpeg
{
    public $ffmpeg = [];

    public $ffmpegArgs = ['-y', '-hide_banner', '-loglevel', 'debug'];

    public function ProgressbarOutput($type, $buffer)
    {
        $outputText = '';
        $this->progress->advance();
        $buffer = str_replace("\n", '', $buffer);
        switch ($buffer) {
            case str_starts_with($buffer, 'frame='):
                $outputText = $buffer;
                break;
        }
        if ('' != $outputText) {
            // Mediatag::$output->write($outputText);
        }

        //        
    }

    public function Outputdebug($type, $buffer)
    {

        // $buffer = str_replace("\n", '', $buffer);

        MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/ffmpeg.log", $buffer . PHP_EOL);
                    // Mediatag::$output->writeln($buffer);
            //  Mediatag::$output->writeln($this->cmdline);

        if (Process::ERR === $type) {
            // echo 'ERR > '.$buffer;
        }
    }

    public function ffmpegExec($cmdOptions, $callback = null)
    {
        // utminfo(func_get_args());

        $this->ffmpeg = [CONFIG['FFMPEG_CMD']];

        $command = array_merge($this->ffmpeg, $this->ffmpegArgs, $cmdOptions);

        $process = new Process($command);
        $process->setTimeout(null);
        $process->start();
        // $process->run($callback);
        // utmdump($process->getOutput());
        $process->wait($callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function convertVideo($file, $output_file)
    {
        // utminfo(func_get_args());

        // $new_file       = str_ireplace('.mov', '.mp4', $file);
        $this->progress = new ProgressBar(Mediatag::$Display->BarSection1, 100);
        $this->progress->setFormat('%bar%');
        // $this->progress->setBarWidth(100);

        $this->progress->start();

        // $cmdOptions = ['-i', $file, '-map', '0', '-c:v', 'copy', '-c:a', 'aac', $new_file];
        $cmdOptions = ['-i', $file, '-qscale', '0', $output_file];
        $callback   = Callback::check([$this, 'ProgressbarOutput']);

        $this->ffmpegExec($cmdOptions, $callback);

        $this->progress->clear();
        // Mediatag::$output->writeln('<comment>Transcoding Video '.$file.'</comment>');

        $dmg_dir = str_replace('/XXX', '/XXX/mkv', \dirname($file));
        FileSystem::createDir($dmg_dir);
        FileSystem::rename($file, $dmg_dir.'/'.basename($file));
    }

    public function repairVideo()
    {
        // utminfo(func_get_args());

        // UTMlog::logNotice('processing with FFMPEG');
        $orig_file    = $this->video_file;
        $new_file     = $orig_file;
        $new_tmp_file = str_replace('.mp4', '_new.mp4', $this->video_file);
        // // UTMlog::logNotice('new file', [$orig_file, $new_file, $new_tmp_file]);

        $cmdOptions = ['-i', $orig_file, '-codec', 'copy', $new_tmp_file];
        $this->ffmpegExec($cmdOptions);

        $dmg_dir = str_replace('/XXX', '/XXX/dmg', $this->video_path);
        FileSystem::createDir($dmg_dir);
        FileSystem::rename($orig_file, $dmg_dir.'/'.$this->video_name);
        FileSystem::rename($new_tmp_file, $new_file);

        $this->write();
    }

    public function ffmegCreateThumb($video_file, $thumbnail, $time = '00:00:30.00')
    {
        // utminfo(func_get_args());

        $cmdOptions = [
            '-ss', $time, '-i', $video_file, '-vf',
            'scale=320:240:force_original_aspect_ratio=decrease',
            '-vframes', '1', $thumbnail,
        ];
        $this->cmdline = $cmdOptions;
        $callback      = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
    }

    public function ffmpegCreateClip($file,$start,$stop,$outputFile){

        // ffmpeg -ss 00:01:00 -to 00:02:00 -i input.mp4 -c copy output.mp4
        $cmdOptions = [
            '-ss', $start, 
            '-to', $stop,
            '-i', $file, '-codec',
            'copy',
            $outputFile
        ];
        $this->cmdline = $cmdOptions;


        $this->progress = new ProgressBar(Mediatag::$Display->BarSection1, 100);
        // $this->progress->setFormat('%bar%');
        $this->progress->setBarWidth(100);

        $this->progress->start();

        // $callback      = Callback::check([$this, 'ProgressbarOutput']);
        $callback      = Callback::check([$this, 'Outputdebug']);

        
        $this->ffmpegExec($cmdOptions, $callback);
        sleep(3);

        $this->progress->clear();


    }
}
