<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data\preview;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Mediatag\Core\Mediatag;
// use Intervention\Image\Image;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\VideoPreview;
use Mediatag\Utilities\GifCreator;
use Symfony\Component\Console\Helper\ProgressBar;

class GifPreviewFiles extends VideoPreview
{
    public $videoRange  = 80;
    public $videoSlides = 10;

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
        $ffprobe  = FFProbe::create($options);
        
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
            // utmdump([$time_secs,TimeCode::fromSeconds($time_secs),$point_file]);

            // Extract the frame.
            $frame = $video->frame(TimeCode::fromSeconds($time_secs));
            $frame->save($point_file);
            // utmdd($point_file);
            // If the frame was successfully extracted, resize it down to
            // 320x200 keeping aspect ratio.
            if (file_exists($point_file)) {
                // utmdump(filesize($point_file));
                // if (filesize($point_file) == 0) {
                $img = new ImageManager(new Driver());
                // $image = $img->read($point_file)->resize(320, 180, function ($constraint) {
                //     $constraint->aspectRatio();
                //     $constraint->upsize();
                // });

                $image = $img->read($point_file)->resize(320, 180);
                $image->tojpeg()->save($point_file);

                // $image->destroy();
            }

            // If the resize was successful, add it to the frames array.
            if (file_exists($point_file)) {
                $frames[] = $point_file;
            }
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
        (new Filesystem())->remove($temp);

        $this->progressBar = true;

        return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
        //        return null;
    }
}
