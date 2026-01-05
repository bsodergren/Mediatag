<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Rotate;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Filters\Video\RotateFilter;
use FFMpeg\Format\Video\X264;
use Mediatag\Commands\Clip\Process;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Traits\MediaFFmpeg;
use Mhor\MediaInfo\MediaInfo;
use UTM\Utilities\Option;

use function array_key_exists;

class RotateProcess extends Process
{
    use MediaFFmpeg;

    public $Marker;

    public $markerArray;

    private $fileArray = [];

    private object $rffmpeg;

    private object $rffprobe;

    public function init()
    {
        $this->rffmpeg  = FFMpeg::create(['timeout' => 3600], Mediatag::$log);
        $this->rffprobe = FFProbe::create();
        $this->fileList();
    }

    public function fileList()
    {
        foreach ($this->VideoList['file'] as $k => $video) {
            $this->fileArray[] = $video['video_file'];
        }
    }

    public function rotateFile()
    {
        foreach ($this->fileArray as $video_file) {
            $this->rotateVideo($video_file);
        }

        return true;
    }

    private function rotateVideo($videoFile)
    {
        $format = new X264;
        // $format->

        $format->on('progress', function ($video, $format, $percentage) {
            Mediatag::$Display->BarSection2->overwrite("<info>$percentage % transcoded</info>");
            //echo "$percentage % transcoded";
        });

        $newVideoFile = str_ireplace('.mp4', '_rotate.mp4', $videoFile);
        $video        = $this->rffmpeg->open($videoFile);
        $video->filters()->rotate(RotateFilter::ROTATE_270); //, ResizeFilter::RESIZEMODE_INSET, true)->synchronize();
        $video->save($format, $newVideoFile);
        Mediatag::$Display->BarSection2->writeln('<comment>Finished</>');

        utmdump([$videoFile, $newVideoFile]);
        $this->backupOrigFile($videoFile, $newVideoFile, 'Rotates');
    }
}
