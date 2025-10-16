<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use const PHP_EOL;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\ProgressListener\VideoProgressListener;
use FFMpeg\Format\Video\X264;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Executable\Callbacks\ProcessCallbacks;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Utilities\Chooser;
use Mediatag\Utilities\ScriptWriter;
use Mhor\MediaInfo\MediaInfo;
use Nette\Utils\Callback;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

use function count;
use function dirname;

trait MediaFFmpeg
{
    use ProcessCallbacks;

    public $progress;

    public $ffmpeg = [];

    public $barAdvance = 50;

    // '-hide_banner', '-nostdin',
    public $ffmpegArgs = ['-y',  '-threads', '1']; // , '-loglevel', 'debug'];

    public $ffmpeg_log = __LOGFILE_DIR__ . '/buffer/ffmpeg.log';

    public $currentFrame = 0;

    public function FrameCountCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        MediaFile::file_append_file($this->ffmpeg_log, $buffer . PHP_EOL);
        if ($this->progress !== null) {
            if (preg_match('/frame=\s([0-9.]+)/', $buffer, $output_array)) {
                $frame              = $output_array[1];
                $adv                = $frame - $this->currentFrame;
                $this->currentFrame = $frame;
                $this->progress->advance($adv);
            }
        }
    }

    public function ProgressbarOutput($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        if ($this->progress !== null) {
            $this->progress->advance();
        }
    }

    public function Outputdebug($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        MediaFile::file_append_file($this->ffmpeg_log, $buffer . PHP_EOL);

        if ($this->progress !== null) {
            $this->progress->advance();
        }
    }

    private function ffExec($exec, $cmdOptions, $callback)
    {
        // $this->ffmpeg = [CONFIG['FFMPEG_CMD']];

        $command = array_merge([$exec], $this->ffmpegArgs, $cmdOptions);

        $process = new Process($command);
        $process->setTimeout(null);
        MediaFile::file_append_file($this->ffmpeg_log, $process->getCommandLine() . PHP_EOL);

        // // utmdump($process->getCommandLine());
        // Mediatag::$ProcessHelper->run(Mediatag::$output,$process,'The process failed :(', function (string $type, string $data): void {
        //     if (Process::ERR === $type) {
        //         echo $data;
        //         // ... do something with the stderr output
        //     } else {
        //         echo $data;
        //         // ... do something with the stdout
        //     }
        // });
        // utmdd($process->getCommandLine());

        if (Option::isTrue('output')) {
            $cmd      = $process->getCommandLine();
            $cmdArray = str_getcsv($cmd, ' ', "'");
            unset($cmdArray[0]);
            // $this->MergedName
            foreach ($cmdArray as $k => $value) {
                $cmdArray[$k] = "'" . $value . "'";
            }

            $obj = new ScriptWriter(str_replace(' ', '_', $this->MergedName) . '.sh', __CURRENT_DIRECTORY__);
            $obj->addCmd('ffmpeg', $cmdArray);
            // utmdd($obj);
            $obj->write();

            return true;
        }

        $process->Run($callback);

        // $process->start();
        // $process->wait($callback);

        //  $process->start();

        if (! $process->isSuccessful()) {
            return false;
            //     throw new ProcessFailedException($process);
            utmdd($process->getCommandLine(), $process->getExitCode(), $process->getErrorOutput());
        }

        return $process;
    }

    public function ffmpegExec($cmdOptions, $callback = null)
    {
        FileSystem::delete($this->ffmpeg_log);

        return $this->ffExec(CONFIG['FFMPEG_CMD'], $cmdOptions, $callback);
    }

    public function convertVideo($file, $output_file)
    {
        $mediaInfo          = new MediaInfo;
        $mediaInfoContainer = $mediaInfo->getInfo($file);
        $videos             = $mediaInfoContainer->getVideos();
        $general            = $mediaInfoContainer->getGeneral();
        $this->frame_count  = $videos[0]->get('frame_count');

        // utminfo(func_get_args());
        // $new_file       = str_ireplace('.mov', '.mp4', $file);
        $this->progress = new ProgressBar(Mediatag::$Display->BarSection1, $this->frame_count);
        // $this->progress->setFormat('%bar%');
        $this->progress->setFormat(' %current%/%max% ,, [%bar%] ,, %percent:3s%%');
        $this->progress->setBarWidth(100);

        $this->progress->start();

        // $cmdOptions = ['-i', $file, '-map', '0', '-c:v', 'copy', '-c:a', 'aac', $new_file];
        $cmdOptions = ['-i', $file, '-qscale', '0', $output_file];
        $callback   = Callback::check([$this, 'ProgressbarOutput']);

        $this->ffmpegExec($cmdOptions, $callback);

        $this->progress->clear();
        // Mediatag::$output->writeln('<comment>Transcoding Video '.$file.'</comment>');

        $dmg_dir = str_replace('/XXX', '/XXX/mkv', dirname($file));
        FileSystem::createDir($dmg_dir);
        FileSystem::rename($file, $dmg_dir . '/' . basename($file));
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
        FileSystem::rename($orig_file, $dmg_dir . '/' . $this->video_name);
        FileSystem::rename($new_tmp_file, $new_file);

        $this->write();
    }

    public function ffmegCreateThumb($video_file, $thumbnail, $time = '00:00:30.00')
    {
        //         $ffmpeg = FFMpeg::create([], Mediatag::$log);
        //         $video = $ffmpeg->open($video_file);

        //         $frame = $video->frame(TimeCode::fromString($time) );
        //         $frame->save($thumbnail);
        // if(file_exists($thumbnail)){
        //     return true;
        // }

        // utmdd($thumbnail);

        //     $video  = $ffmpeg->open($videoFiles[0]);

        $cmdOptions = [
            '-ss', $time, '-i', $video_file, '-vf',
            'scale=320:240:force_original_aspect_ratio=decrease',
            '-vframes', '1', $thumbnail,
        ];
        $this->cmdline = $cmdOptions;
        $callback      = Callback::check([$this, 'ProgressbarOutput']);

        return $this->ffmpegExec($cmdOptions, $callback);
    }

    public function ffmpegCreateClip($file, $marker, $idx)
    {
        $outputFile = $this->getClipFilename($file);
        $outputFile = str_replace('.mp4', '_' . $marker['text'] . '_' . $idx . '.mp4', $outputFile);
        FileSystem::createDir(dirname($outputFile));

        if (file_exists($outputFile)) {
            if (! Chooser::changes(' Overwrite File', 'overwrite', __LINE__)) {
                return;
            }
        }

        $cmdOptions = [
            '-v', 'debug',
            '-ss', $marker['start'],
            '-to', $marker['end'],
            '-i', $file, '-codec',
            'copy',
            $outputFile,
        ];
        $this->cmdline = $cmdOptions;
        // utmdump($cmdOptions);
        // $callback = Callback::check([$this, 'ProgressbarOutput']);
        $this->progress->startIndicator('Clipping ' . $marker['text'] . ' at ' . $marker['start'] . ' to ' . $marker['end']);

        $callback = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
        sleep(3);
        $this->progress->finishIndicator('Finished ' . $marker['text']);
    }

    public function createCompilation($files, $ClipName, $name)
    {
        $duration         = Option::getValue('dur', true, 3);
        $type             = Option::getValue('type');
        $this->MergedName = $name;
        $this->clipName   = $ClipName;

        $fileCount = count($files);
        Mediatag::$output->writeln('<info>Merging ' . $fileCount . ' files</info>');
        Mediatag::$output->writeln('<info>Info compilation called  ' . $name . ' </info>');

        $ffmpeg = FFMpeg::create();

        // $advancedMedia = $ffmpeg->openAdvanced($files);

        // $advancedMedia->filters()->pad()
        // // //     ->custom('[0:v][1:v]', 'xfade=transition=radial', '[v]');

        // $format = new X264('aac', 'libx264');
        // $format->on('progress', function ($advancedMedia, $format, $percentage) {
        //     Mediatag::$output->write('<info>Info compilation called  ' . $percentage . ' </info>');
        //     utmdump("$percentage % transcoded");
        // });

        // $advancedMedia
        //     ->map([], $format, $ClipName)
        //     ->save();
        Mediatag::$Display->BarSection1->writeln('<file>Merging files</>');

        $video  = $ffmpeg->open($files[0]);
        $format = new X264;
        // $format->setAudioCodec("libmp3lame");

        $format->on('progress', function ($video, $format, $percentage) {
            // Mediatag::$Display->BarSection2->overwrite('<info> ' . $percentage . ' </info>');
            Mediatag::$Display->BarSection2->overwrite("<info>$percentage % transcoded</info>");
        });

        // utmdd($files);
        $video->concat($files)->saveFromDifferentCodecs($format, $ClipName);
        Mediatag::$Display->BarSection2->writeln('<comment>Finished</>');

        // $cmd = $this->generateFfmpegCommand($files, $type, $duration);

        // if ($cmd === true) {
        //     return true;
        // }
        // $cmdArray      = array_merge($cmd, [$ClipName]);
        // $this->cmdline = $cmdArray;

        // $this->progress = new MediaBar($this->clipLength, 'one', 120);
        // MediaBar::addFormat('%current:4s%/%max:4s% -- [%bar%] -- %percent:3s%%');
        // // $this->progress->setMsgFormat()->setMessage("All Files",'message')->newbar();
        // $this->progress->start();
        // // $this->progress->startIndicator('Creating Compilation '.$name);
        // $callback = Callback::check([$this, 'FrameCountCallback']);

        // $this->ffmpegExec($cmdArray, $callback);
    }

    public function ffmpegCreateChapterVideo($file, $markerFile)
    {
        $outputFile = str_replace('.mp4', '_chapters.mp4', $file);

        if (file_exists($outputFile)) {
            if (! Chooser::changes(' Overwrite File', 'overwrite', __LINE__)) {
                return;
            }
        }

        $cmdOptions = [
            '-y',
            '-i', $file,
            '-i', $markerFile,
            '-map_metadata',
            '1',
            '-c',
            'copy',
            $outputFile,
        ];
        $this->cmdline  = $cmdOptions;
        $this->progress = new MediaBar(200, 'one', 120);
        MediaBar::addFormat('%current:4s%,%max:4s%,[%bar%],%percent:3s%%', 'ChapterVideos');
        $this->progress->setMsgFormat('ChapterVideos');
        $callback = Callback::check([$this, 'Outputdebug']);
        $this->ffmpegExec($cmdOptions, $callback);
    }
}
