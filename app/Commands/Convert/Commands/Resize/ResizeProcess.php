<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert\Commands\Resize;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use FFMpeg\Format\Video\X264;
use Mediatag\Utilities\Chooser;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use Mediatag\Commands\Convert\Process;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\Filesystem\MediaFilesystem;


class ResizeProcess extends Process
{

    use ResizeHelper;

    private $FFProbe;
    public $ffmpeg;

    private $fileDims = [];


    public function execResize()
    {
        MediaFinder::$depth = 1;
        $this->file_array   = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, "*.mp4");
        $this->FFProbe      = FFProbe::create(array(
            'timeout' => 3600, // The timeout for the underlying process

        ), Mediatag::$log);


        $this->ffmpeg = FFMpeg::create(array(
            'timeout' => 3600, // The timeout for the underlying process
        ), Mediatag::$log);

    }

    public function ResizeFiles()
    {

        $maxWidth = 0;
        $maxRatio = 0;

        foreach ($this->file_array as $k => $file) {

            // utmdump($this->FFProbe);
            $videoInfo = $this->FFProbe
                ->streams($file) // extracts streams informations
                ->videos()                      // filters video streams
                ->first();

            $width = $videoInfo->getDimensions()->getWidth();
            $ratio = $videoInfo->getDimensions()->getRatio()->getValue();
            // utmdd($width, $ratio);
            if ($width > $maxWidth) {
                $maxWidth = $width;
            }


            unset($videoInfo);

        }

        foreach ($this->file_array as $k => $file) {
            $videoInfo = $this->FFProbe
                ->streams($file) // extracts streams informations
                ->videos()                      // filters video streams
                ->first();

            $height = $videoInfo->getDimensions()->getRatio()->calculateHeight($maxWidth);

            $this->fileDims[$k] = ['width' => $maxWidth, 'height' => $height];

        }



        foreach ($this->file_array as $k => $file) {
            $this->doResize($k, $file);
        }

        exit(0);
    }


    private function doResize($key, $video_file)
    {

        $mp4_file   = basename($video_file, '.mp4');
        $mp4_file   = basename($mp4_file, '.MP4') . '.mp4';
        $mp4_dir    = dirname($video_file, 1) . DIRECTORY_SEPARATOR . 'Resized' . DIRECTORY_SEPARATOR;
        $moved_file = dirname($video_file, 1) . DIRECTORY_SEPARATOR . 'Resized_moved' . DIRECTORY_SEPARATOR;

        if (!Mediatag::$filesystem->exists($mp4_dir)) {
            Mediatag::$filesystem->mkdir($mp4_dir);
        }



        if (!Mediatag::$filesystem->exists($moved_file)) {
            Mediatag::$filesystem->mkdir($moved_file);
        }

        $moved_file = $moved_file . basename($video_file);
        $mp4_file   = $mp4_dir . $mp4_file;

        if (file_exists($mp4_file)) {
            if (
                !Chooser::changes(' Overwrite File ' . basename($mp4_file), 'overwrite', __LINE__)
                && Option::isFalse('yes')
            ) {
                Mediatag::$output->writeln('<info> existing file</info>');
                return;
            } else {
                Mediatag::$output->writeln('<info> Deleting existing file</info>');
                unlink($mp4_file);
            }
        }


        Mediatag::$output->writeln('<comment>Transcoding file ' . basename($video_file) . ' </>');
        $video = $this->ffmpeg->open($video_file);

        $format = new X264();
        // $format->on('progress', function ($video, $format, $percentage) {
        //     echo "$percentage % transcoded";
        // });

        $format->on('progress', function ($advancedMedia, $format, $percentage) {
            Mediatag::$Display->VideoInfoSection->overwrite('<info>Info compilation called  ' . $percentage . ' </info>');
        });

        // $format
        //     ->setKiloBitrate(1000)
        //     ->setAudioChannels(2)
        //     ->setAudioKiloBitrate(256);

        $height = $this->fileDims[$key]['height'];
        $width  = $this->fileDims[$key]['width'];

        $video->filters()->resize(new Dimension($width, $height), ResizeFilter::RESIZEMODE_FIT, true);
        $video->save($format, $mp4_file);

        MediaFilesystem::renameFile($video_file, $moved_file);
    }
}
