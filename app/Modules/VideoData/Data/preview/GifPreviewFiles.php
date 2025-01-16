<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data\preview;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Mediatag\Core\Mediatag;
use FFMpeg\Coordinate\TimeCode;
use Mediatag\Utilities\GifCreator;
use Intervention\Image\ImageManager;
// use Intervention\Image\Image;
use Intervention\Image\Drivers\Gd\Driver;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\VideoData\Data\VideoPreview;
use Symfony\Component\Console\Helper\ProgressBar;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\VideoData\Data\preview\GeneratePreview;
use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;

class GifPreviewFiles extends VideoPreview
{
    public $videoRange  = 80;
    public $videoSlides = 15;

    public object $progress;

    public function oobuild_video_thumbnail()
    {
       

        
$options=       [
     'width'=>'320', 'height'=> '240', 'numFrames'=> '10','gifski'=>['fps','3'],
        'input'=> $this->video_file,
        'output'=> $this->previewName];

$video = new GeneratePreview();
$r = $video->processVideo($options);
utmdump($r);
        

        return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
    }

    public function build_video_thumbnail_o()
    {
        $ffmpeg = '/usr/bin/ffmpeg';

        // the input video file
        $video = $this->video_file;

        // extract one frame at 10% of the length, one at 30% and so on
        $frames = ['10%', '30%', '50%', '70%', '90%'];

        // set the delay between frames in the output GIF
        $joiner = new Thumbnail_Joiner(50);
        // loop through the extracted frames and add them to the joiner object
        foreach (new Thumbnail_Extractor($video, $frames, '320x240', $ffmpeg) as $key => $frame) {
            $joiner->add($frame);
        }
        $joiner->save($this->previewName);

        return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
    }

    public function build_video_thumbnail()
    {
        // Create a temp directory for building.
        $temp    = __PLEX_VAR_DIR__.'/build/'.md5($this->video_file);
        $options = [
            'temporary_directory' => $temp,
            'loglevel'            => 'quiet',
        ];
        (new Filesystem())->mkdir($temp);

        // Use FFProbe to get the duration of the video.
        $ffprobe = FFProbe::create($options);

        $duration = floor($ffprobe
            ->format($this->video_file)
            ->get('duration'));

        // If we couldn't get the direction or it was zero, exit.
        if (empty($duration)) {
            return null;
        }
        // Create an FFMpeg instance and open the video.
        $ffmpeg = FFMpeg::create($options);
        $video  = $ffmpeg->open($this->video_file);

        // This array holds our "points" that we are going to extract from the
        // video. Each one represents a percentage into the video we will go in
        // extracitng a frame. 0%, 10%, 20% ..
        $videoRange  = $this->videoRange;
        $videoSlides = $this->videoSlides;
        $points      = array_map(function ($n) { return round($n, 0); }, range(1, $videoRange, $videoRange / $videoSlides));
        // $points = range(0, $this->videoRange, $this->videoRange / $this->videoSlides);
        // This will hold our finished frames.
        $frames      = [];
        $progressBar = new ProgressBar(Mediatag::$output, \count($points));

        $progressBar->setFormat('<comment>%no:4s%</comment> <fg=red>Writing Preview</>  <info>%message%</info> <fg=cyan;options=bold>[%bar%]</> %percent:3s%%');
        $progressBar->setMessage($this->fileCount--, 'no');

        $message = $this->setMessage($this->video_file);

        $progressBar->setMessage($message, 'message');        // $progressBar->setBarWidth("100");

        $progressBar->start();
        foreach ($points as $point) {
            // Point is a percent, so get the actual seconds into the video.
            $time_secs = floor($duration * ($point / 100));
            $progressBar->advance();

            // Created a var to hold the point filename.
            $point_file = "$temp/$point.jpg";
            $jpg_file   = "$temp/x_$point.jpg";
            utmdump([$time_secs, $duration, $point_file]);

            // Extract the frame.

            try {
                $frame = $video->frame(TimeCode::fromSeconds($time_secs));
            } catch (ExecutionFailureException $e) {
                // $this->cleanupTemporaryFile($pathfile);
            }
            $frame->save($point_file);

            // utmdd($point_file);
            // If the frame was successfully extracted, resize it down to
            // 320x200 keeping aspect ratio.
            if (file_exists($point_file)) {
                utmdump([filesize($point_file), $point_file]);
                // if (filesize($point_file) == 0) {
                $img = new ImageManager(new Driver());
                // $image = $img->read($point_file)->resize(320, 180, function ($constraint) {
                //     $constraint->aspectRatio();
                //     $constraint->upsize();
                // });

                $image = $img->read($point_file)->resize(320, 240);
                $image->tojpeg()->save($jpg_file);
                utmdump([filesize($jpg_file), $jpg_file]);

                // $image->destroy();
            }

            // If the resize was successful, add it to the frames array.
            if (file_exists($jpg_file)) {
                $frames[] = $jpg_file;
            }

            $point_frames[] = $point_file;
        }
        $progressBar->finish();
        Mediatag::$output->writeln('');

        // If we have frames that were successfully extracted.
        if (!empty($frames)) {
            // We show each frame for 100 ms.
            $durations = array_fill(0, \count($frames), 100);

            // Create a new GIF and save it.
            $gc = new GifCreator();
            $gc->create($frames, $durations, 0);
            file_put_contents($this->previewName, $gc->getGif());

            // Remove all the temporary frames.
            foreach ($frames as $file) {
                unlink($file);
            }
        }
        foreach ($point_frames as $file) {
            unlink($file);
        }

        (new Filesystem())->remove($temp);

        $this->progressBar = true;

        return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
        //        return null;
    }
}
