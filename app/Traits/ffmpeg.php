<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem;
use UTM\Bundle\Monolog\UTMLog;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;

trait ffmpeg
{
    public $ffmpeg     = [];

    public $ffmpegArgs = ['-y', '-hide_banner', '-loglevel', 'verbose'];

    public function ProgressbarOutput($type, $buffer)
    {
        $this->progress->advance();
    }

    public function Outputdebug($type, $buffer)
    {

        if (Process::ERR === $type) {
            // Mediatag::$output->writeln($buffer);
            //  Mediatag::$output->writeln($this->cmdline);
            //echo 'ERR > '.$buffer;

        }
    }

    public function ffmpegExec($cmdOptions, $callback = null)
    {
        utminfo(func_get_args());

        $this->ffmpeg = [CONFIG['FFMPEG_CMD']];

        $command      = array_merge($this->ffmpeg, $this->ffmpegArgs, $cmdOptions);

        $process      = new Process($command);

        $process->setTimeout(null);
        $process->start();
        $process->wait($callback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public function convertVideo($file)
    {
        utminfo(func_get_args());

        $new_file       = str_replace('.mkv', '.mp4', $file);
        $this->progress = new ProgressBar($this->barSection);
        $this->progress->setFormat('%bar%');
        $this->progress->start();

        $cmdOptions     = ['-i', $file, '-map', '0', '-c:v', 'copy', '-c:a', 'aac', $new_file];
        $callback       = Callback::check([$this, 'ProgressbarOutput']);

        $this->ffmpegExec($cmdOptions, $callback);

        $this->progress->finish();
        $dmg_dir        = str_replace('/XXX', '/XXX/mkv', \dirname($file));
        FileSystem::createDir($dmg_dir);
        FileSystem::rename($file, $dmg_dir . '/' . basename($file));
    }

    public function repairVideo()
    {
        utminfo(func_get_args());

        // UTMlog::logNotice('processing with FFMPEG');
        $orig_file    = $this->video_file;
        $new_file     = $orig_file;
        $new_tmp_file = str_replace('.mp4', '_new.mp4', $this->video_file);
        // // UTMlog::logNotice('new file', [$orig_file, $new_file, $new_tmp_file]);

        $cmdOptions   = ['-i', $orig_file, '-codec', 'copy', $new_tmp_file];
        $this->ffmpegExec($cmdOptions);

        $dmg_dir      = str_replace('/XXX', '/XXX/dmg', $this->video_path);
        FileSystem::createDir($dmg_dir);
        FileSystem::rename($orig_file, $dmg_dir . '/' . $this->video_name);
        FileSystem::rename($new_tmp_file, $new_file);

        $this->write();
    }

    public function ffmegCreateThumb($video_file, $thumbnail, $time = '00:00:30.00')
    {
        utminfo(func_get_args());

        $cmdOptions    = [
            '-ss', $time, '-i', $video_file, '-vf',
            'scale=320:240:force_original_aspect_ratio=decrease',
            '-vframes', '1', $thumbnail,
        ];
        $this->cmdline = $cmdOptions;
        $callback      = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
    }
}
