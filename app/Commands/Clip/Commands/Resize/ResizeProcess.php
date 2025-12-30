<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Resize;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Filters\Video\ResizeFilter;
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

class ResizeProcess extends Process
{
    use MediaFFmpeg;

    public $Marker;

    public $markerArray;

    private $fileArray = [];

    private object $rffmpeg;

    private object $rffprobe;

    private function init()
    {
        $this->rffmpeg  = FFMpeg::create([   'timeout'          => 3600], Mediatag::$log);
        $this->rffprobe = FFProbe::create();
        $this->fileList();
    }

    private function fileList()
    {
        utmdd([__METHOD__, $this->VideoList]);
        foreach ($this->VideoList['file'] as $k => $video) {
            $this->fileArray[] = $video['video_file'];
        }
    }

    public function resizeFile()
    {
        $dimensions = Option::getValue('dim', 1);

        [$width, $height] = explode('X', strtoupper($dimensions));
        $resizeVideos     = [];

        foreach ($this->fileArray as $video_file) {
            $dims = $this->getVideoDimensions($video_file);
            if ($dims['width'] == $width
            && $dims['height'] == $height) {
                Mediatag::$Display->BarSection1->writeln('<file>No resize ' . basename($video_file) . '</>');

                continue;
            }
            $resizeVideos[] = $video_file;
        }

        $nVideos = count($resizeVideos);
        if ($nVideos > 0) {
            foreach ($resizeVideos as $video_file) {
                Mediatag::$Display->BarSection1->writeln($nVideos . '<file>Resize file ' . basename($video_file) . '</>');
                $this->resizeVideo($video_file, $width, $height);
                $nVideos--;
            }
        }

        return true;
    }

    private function getVideoDimensions($video_file)
    {
        //$ffprobe = FFProbe::create();
        $stream         = $this->rffprobe->streams($video_file);
        $video          = $stream->videos()->first();
        $dims['width']  = $video->get('width');
        $dims['height'] = $video->get('height');
        $codec_name     = $video->get('codec_name');

        // $height = $video->get('height');

        utmdump($stream->all());

        return $dims;
    }

    private function resizeVideo($videoFile, $width, $height)
    {
        $format = new X264;
        // $format->

        $format->on('progress', function ($video, $format, $percentage) {
            Mediatag::$Display->BarSection2->overwrite("<info>$percentage % transcoded</info>");
            //echo "$percentage % transcoded";
        });

        $dimension    = new Dimension($width, $height);
        $newVideoFile = str_ireplace('.mp4', '_new.mp4', $videoFile);
        $video        = $this->rffmpeg->open($videoFile);
        $video->filters()->pad($dimension); //, ResizeFilter::RESIZEMODE_INSET, true)->synchronize();
        $video->save($format, $newVideoFile);
        Mediatag::$Display->BarSection2->writeln('<comment>Finished</>');

        $this->getVideoDimensions($newVideoFile);

        $this->backupOrigFile($videoFile, $newVideoFile, 'Resized');
    }
}
