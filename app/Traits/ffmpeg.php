<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Utilities\Chooser;
use Mhor\MediaInfo\MediaInfo;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;

trait ffmpeg
{
    public $progress;
    public $ffmpeg = [];

    public $barAdvance = 50;

    public $ffmpegArgs = ['-y', '-hide_banner'];

    public function FrameCountCallback($type, $buffer)
    {
    }

    public function ProgressbarOutput($type, $buffer)
    {
        $outputText = '';
        $buffer     = str_replace("\n", '', $buffer);
        // utmdd($buffer);

        if (null !== $this->progress) {
            $this->progress->advance();
        }
    }

    public function Outputdebug($type, $buffer)
    {
        // $buffer = str_replace("\n", '', $buffer);
        // utmdd($buffer);
        if (null !== $this->progress) {
            $this->progress->advance();
        }
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
        $this->ffExec(CONFIG['FFMPEG_CMD'], $cmdOptions, $callback);
    }

    public function ffmpegProbe($cmdOptions, $callback = null)
    {
        $probe = $this->ffExec(CONFIG['FFPROBE_CMD'], $cmdOptions, $callback);

        return json_decode($probe->getOutput(), true);
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

    public function ffmprobeGetFrames($file, $start, $stop)
    {
        $cache_name = md5($start.'%'.$stop.basename($file));
        $frame_json = MediaCache::get($cache_name);

        if (false !== $frame_json) {
            if (Chooser::changes(__METHOD__.' Overwrite frameCount', 'overwrite', __LINE__)) {
                $frame_json = false;
                // Mediatag::$output->writeln('overwrite frameCount');
            }
        }

        if (false === $frame_json) {
            $cmdOptions = [
                '-read_intervals', $start.'%'.$stop,
                '-count_frames',
                '-show_entries', 'stream', '-of', 'json',
                // '-v','trace',
                '-i', $file,
            ];
            $this->cmdline = $cmdOptions;

            // $callback = Callback::check([$this, 'FrameCountCallback']);
            // utmdd($cmdOptions);
            // Mediatag::$output->writeln('Getting Frame Count');

            $this->progress->startIndicator('Getting frames from '.$start.' to '.$stop.' for '.basename($file));

            $callback   = Callback::check([$this, 'Outputdebug']);
            $frame_json = $this->ffmpegProbe($cmdOptions, $callback);
            $r          = MediaCache::put($cache_name, $frame_json);
            $this->progress->finishIndicator('Finished getting frames');
        }

        return $frame_json;
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

    public function createCompilation($listFile, $ClipName, $name)
    {
        $cmdOptions = [
            '-v', 'debug',
            // '-filter_complex', 'xfade=transition=fade:duration=2:offset=5',
            '-safe', '0',
            '-f', 'concat',
            '-i', $listFile,
            '-codec', 'copy',
            $ClipName,
        ];
        $this->cmdline = $cmdOptions;

        $this->progress->startIndicator('Creating Compilation '.$name);
        $callback = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
        $this->progress->finishIndicator('Finished Compilation '.$name);

        return true;
    }
}
