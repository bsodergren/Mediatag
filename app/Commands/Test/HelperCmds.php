<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Mediatag\Core\Mediatag;
use Nette\Utils\FileSystem;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\FrameRate;
use FFMpeg\Filters\Video\ResizeFilter;
use FFMpeg\Filters\Video\RotateFilter;

trait HelperCmds
{
    public $videoFile;

    public function clip()
    {
        $videoFile = $this->videoFile[0];
        $timeCodes = [245, 445, 845,1045,1345,1845];

        $path = dirname($videoFile)."/Clips/";
        $filename = $path.basename($videoFile,".mp4");

        FileSystem::createDir($path);
        //utmdd($filename);

        // $new_file = str_replace('.mp4', '_test.mp4', $this->videoFile);

        // Mediatag::$log->info("Video Files \n{0} \n{1}",[$this->videoFile,$new_file]);
        //         $ffprobe = FFProbe::create([],  Mediatag::$log);
        //         $duration = $ffprobe->format($this->videoFile)->get('duration');

        // utmdd($duration);
        $ffmpeg = FFMpeg::create([], Mediatag::$log);
        $video  = $ffmpeg->open($videoFile);

        // // $video = $ffmpeg->open( '/path/to/video' );
        // $video
        //     ->gif(TimeCode::fromSeconds(35), new Dimension(640, 480), 15)
        //     ->save($new_file);

        $format = new X264();
        $format->on('progress', function ($video, $format, $percentage) {
            echo "$percentage % transcoded";
        });
        
        foreach ($timeCodes as $i =>$code) {
            utmdump($code);
            $clip = $video->clip(TimeCode::fromSeconds($code), TimeCode::fromSeconds(5));
            $clip->filters()->resize(new Dimension(320, 240), ResizeFilter::RESIZEMODE_INSET, true);
            $clip->save($format, $filename."_".$i.".mp4");
        }
    }

    public function rotate()
    {
        $videoFile = $this->videoFile[0];

        $new_file = str_replace('.mp4', '_test.mp4', $videoFile);
        // utmdd( [__METHOD__,$this->videoFile, $new_file ]);

        $ffmpeg = FFMpeg::create([], Mediatag::$log);
        $video  = $ffmpeg->open($videoFile);
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

    public function combine()
    {
        $videoFile = $this->videoFile[0];
        
        $new_file = rtrim($videoFile,"0.mp4");
        // $new_file = rtrim($new_file,"_0");
        $new_file = $new_file."merged.mp4";
// utmdd($new_file);
        $ffmpeg = FFMpeg::create([], Mediatag::$log);

        $video = $ffmpeg->open($videoFile);
        $video
            ->concat($this->videoFile)
            ->saveFromSameCodecs($new_file, true);



            $ffprobe = FFProbe::create();
            $duration = (int) $ffprobe->format($new_file)->get('duration');
            
            // The gif will have the same dimension. You can change that of course if needed.
            $dimensions = $ffprobe->streams($new_file)->videos()->first()->getDimensions();
            
            $gifPath = str_replace(".mp4",".gif",$new_file);
            
            // Transform
            $ffmpeg = FFMpeg::create();
            $ffmpegVideo = $ffmpeg->open($new_file);
            $ffmpegVideo->filters()->framerate(new FrameRate(10), 10);
            $ffmpegVideo->gif(TimeCode::fromSeconds(0), $dimensions, $duration)->save($gifPath);

            



    }
}
