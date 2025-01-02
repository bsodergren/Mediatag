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

class Ffmpeg
{

    use FfExec;
    public $ffmpeg = [];

    public $barAdvance = 50;

    public $ffmpegArgs = ['-y', '-hide_banner'];

    public function ffmpegExec($cmdOptions, $callback = null)
    {
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

    public function ffmpegCreateClip($file, $marker, $idx)
    {
        $outputFile = str_replace('/XXX', '/Clips', $file);
        $outputFile = str_replace('.mp4', '_'.$marker['text'].'_'.$idx.'.mp4', $outputFile);
        FileSystem::createDir(\dirname($outputFile));

      
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
        $this->progress->start("Clipping ".$marker['text'] . " at ".$marker['start']. " to ". $marker['end'] );

        $callback      = Callback::check([$this, 'Outputdebug']);

        $this->ffmpegExec($cmdOptions, $callback);
        sleep(3);
        $this->progress->finish('Finished ' . $marker['text']);

    }
}