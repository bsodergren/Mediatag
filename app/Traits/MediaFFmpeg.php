<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Traits\Callables\ProcessCallbacks;
use Mediatag\Utilities\Chooser;
use Mhor\MediaInfo\MediaInfo;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

trait MediaFFmpeg
{
    use ProcessCallbacks;

    public $progress;
    public $ffmpeg = [];

    public $barAdvance = 50;

    public $ffmpegArgs = ['-y', '-hide_banner', '-threads', '1', '-loglevel', 'error', '-stats'];

    public $ffmpeg_log = __LOGFILE_DIR__.'/buffer/ffmpeg.log';

    public function FrameCountCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        MediaFile::file_append_file($this->ffmpeg_log, $buffer.\PHP_EOL);

        if (null !== $this->progress) {
            if (preg_match('/fps=\s([0-9.]+)/', $buffer, $output_array)) {
                $this->progress->advance($output_array[1]);
            }
        }
    }

    public function ProgressbarOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        if (null !== $this->progress) {
            $this->progress->advance();
        }
    }

    public function Outputdebug($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        MediaFile::file_append_file($this->ffmpeg_log, $buffer.\PHP_EOL);

        if (null !== $this->progress) {
            $this->progress->advance();
        }
    }

    private function ffExec($exec, $cmdOptions, $callback)
    {
        // $this->ffmpeg = [CONFIG['FFMPEG_CMD']];

        $command = array_merge([$exec], $this->ffmpegArgs, $cmdOptions);

        $process = new Process($command);
        $process->setTimeout(null);
        MediaFile::file_append_file($this->ffmpeg_log, $process->getCommandLine().\PHP_EOL);

        // utmdump($process->getCommandLine());
        // $process->start($callback);
        $process->start();
        $process->wait($callback);

        //  $process->start();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    public function ffmpegExec($cmdOptions, $callback = null)
    {
        FileSystem::delete($this->ffmpeg_log);
        $this->ffExec(CONFIG['FFMPEG_CMD'], $cmdOptions, $callback);
    }

    public function convertVideo($file, $output_file)
    {
        $mediaInfo          = new MediaInfo();
        $mediaInfoContainer = $mediaInfo->getInfo($file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();
        $this->frame_count  = $videos[0]->get('frame_count');

        // utminfo(func_get_args());
        // $new_file       = str_ireplace('.mov', '.mp4', $file);
        $this->progress = new ProgressBar(Mediatag::$Display->BarSection1, $this->frame_count);
        // $this->progress->setFormat('%bar%');
        $this->progress->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $this->progress->setBarWidth(100);

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
        // $ffmpeg = FFMpeg::create();
        // $video  = $ffmpeg->open($video_file);
        // $video->filters()->resize(new Dimension(320, 240));
        // utmdump(TimeCode::fromString($time));
        // $frame = $video->frame(TimeCode::fromString($time));
        // utmdump($frame->getTimeCode());
        // $frame->save($thumbnail);

        $cmdOptions = [
            '-ss', $time, '-i', $video_file, '-vf',
            'scale=320:240:force_original_aspect_ratio=decrease',
            '-vframes', '1', $thumbnail,
        ];
        $this->cmdline = $cmdOptions;
        $callback      = Callback::check([$this, 'ProgressbarOutput']);

        $this->ffmpegExec($cmdOptions, $callback);
    }

    public function ffmpegCreateClip($file, $marker, $idx)
    {
        $outputFile = $this->getClipFilename($file);
        $outputFile = str_replace('.mp4', '_'.$marker['text'].'_'.$idx.'.mp4', $outputFile);
        FileSystem::createDir(\dirname($outputFile));

        if (file_exists($outputFile)) {
            if (!Chooser::changes(' Overwrite File', 'overwrite', __LINE__)) {
                return;
            }
            // Mediatag::$output->writeln('overwrite file');
        }
        // utmdd('fr');
        // ffmpeg -ss 00:01:00 -to 00:02:00 -i input.mp4 -c copy output.mp4
        $cmdOptions = [
            '-v', 'debug',
            '-ss', $marker['start'],
            '-to', $marker['end'],
            '-i', $file, '-codec',
            'copy',
            $outputFile,
        ];
        $this->cmdline = $cmdOptions;

        // $callback = Callback::check([$this, 'ProgressbarOutput']);
        $this->progress->startIndicator('Clipping '.$marker['text'].' at '.$marker['start'].' to '.$marker['end']);

        $callback = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
        sleep(3);
        $this->progress->finishIndicator('Finished '.$marker['text']);
    }

    public function createCompilation($files, $ClipName, $name)
    {
        $duration       = Option::getValue('dur', true, 3);
        $type           = Option::getValue('type');
        $this->clipName = $ClipName;
        $cmd            = $this->generateFfmpegCommand($files, $type, $duration);
        if (true === $cmd) {
            return true;
        }
        $cmdArray = array_merge($cmd, [$ClipName]);

        $this->cmdline = $cmdArray;

        $this->progress = new MediaBar(($this->clipLength * 1000) / 30, 'one', 120);
        MediaBar::addFormat('%current:4s%/%max:4s% [%bar%] %percent:3s%%');
        // $this->progress->setMsgFormat()->setMessage("All Files",'message')->newbar();
        $this->progress->start();
        // $this->progress->startIndicator('Creating Compilation '.$name);
        $callback = Callback::check([$this, 'FrameCountCallback']);

        $this->ffmpegExec($cmdArray, $callback);
    }


}
