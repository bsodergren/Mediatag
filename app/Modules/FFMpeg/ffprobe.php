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

class Ffprobe
{

    use FfExec;
    public $ffmpeg = [];

    public $barAdvance = 50;

    public $ffmpegArgs = ['-y', '-hide_banner'];


    public function ffmpegProbe($cmdOptions, $callback = null)
    {
        $probe = $this->ffExec(CONFIG['FFPROBE_CMD'], $cmdOptions, $callback);

        return json_decode($probe->getOutput(), true);
    }
    
    public function ffmprobeGetFrames($file, $start, $stop)
    {
        $cache_name = md5($start.'%'.$stop.basename($file));
        $frame_json = MediaCache::get($cache_name);
        if (false === $frame_json) {
            $cmdOptions = [
                '-read_intervals', $start.'%'.$stop,
                '-count_frames',
                '-show_entries', 'stream', '-of', 'json',
                '-v', 'quiet',
                '-i', $file,
            ];
            $this->cmdline = $cmdOptions;

            // $callback = Callback::check([$this, 'FrameCountCallback']);
            // utmdd($cmdOptions);

            $this->progress->start("Getting frames from " . $start ." to " . $stop . " for " . basename($file) );

            $callback      = Callback::check([$this, 'Outputdebug']);
            $frame_json = $this->ffmpegProbe($cmdOptions, $callback);
            $r          = MediaCache::put($cache_name, $frame_json);
            $this->progress->finish("Finished getting frames");
        }

        return $frame_json;
    }
}