<?php
namespace Mediatag\Commands\Test;

use FFMpeg\FFMpeg;
use Mediatag\Core\Mediatag;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\RotateFilter;



trait HelperCmds
{

    public $videoFile;

    public function rotate()
    {


        $new_file = str_replace(".mp4","_test.mp4",$this->videoFile);
        // utmdd( [__METHOD__,$this->videoFile, $new_file ]);

        $ffmpeg = FFMpeg::create([],$this->logger );
        $video = $ffmpeg->open($this->videoFile );
        $video->filters()->rotate(RotateFilter::ROTATE_270)->synchronize();

        $video->save(new X264(), $new_file);
        // $video
        //     ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
        //     ->save('frame.jpg');
        // $video
        //     ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4')
        //     ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
        //     ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');


    }



}