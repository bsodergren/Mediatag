<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section\preview;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use GifCreator\GifCreator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Mediatag\Core\Mediatag;
// use Intervention\Image\Image;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\VideoPreview;

use function count;

class VideoPreviewFiles extends VideoPreview
{
    // public function getText()
    // {
    //     // utminfo(func_get_args());

    //     return $this->returnText . basename($this->video_name, '.mp4') . '.gif';// .' for '.basename($this->video_file);

    // }

    public static function gifToVideo($file)
    {
        // utminfo(func_get_args());

        return str_replace('.gif', '.mp4', __PLEX_HOME__ . str_replace(__INC_WEB_PREVIEW_DIR__, '', $file));
    }

    public static function videoToPreview($file)
    {
        // utminfo(func_get_args());

        return str_replace('.mp4', '.gif', __INC_WEB_PREVIEW_DIR__ . str_replace(__PLEX_HOME__, '', $file));
    }

    public function build_video_thumbnail()
    {
        // utminfo(func_get_args());

        // Create a temp directory for building.
        $temp    = __PLEX_VAR_DIR__ . '/build';
        $options = [
            'temporary_directory' => $temp,
            'timeout'             => 3600,
        ];

        (new Filesystem)->mkdir($temp);
        // Use FFProbe to get the duration of the video.
        $ffprobe  = FFProbe::create($options);
        $duration = floor($ffprobe
            ->format($this->video_file)
            ->get('duration'));

        // If we couldn't get the direction or it was zero, exit.
        if (empty($duration)) {
            return;
        }

        // Create an FFMpeg instance and open the video.
        $ffmpeg = FFMpeg::create($options, Mediatag::$log);
        $video  = $ffmpeg->open($this->video_file);

        // This array holds our "points" that we are going to extract from the
        // video. Each one represents a percentage into the video we will go in
        // extracitng a frame. 0%, 10%, 20% ..
        $points = range(0, 100, 10);

        // This will hold our finished frames.
        $frames = [];

        foreach ($points as $point) {
            // Point is a percent, so get the actual seconds into the video.
            $time_secs = floor($duration * ($point / 100));

            // Created a var to hold the point filename.
            $point_file = "$temp/$point.jpg";
            // utmdump($time_secs);
            // Extract the frame.
            $frame = $video->frame(TimeCode::fromSeconds($time_secs));
            $frame->save($point_file);
            // utmdd($point_file);
            // If the frame was successfully extracted, resize it down to
            // 320x200 keeping aspect ratio.
            if (file_exists($point_file)) {
                $img   = new ImageManager(new Driver);
                $image = $img->read($point_file)->resize(320, 180, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                $image->tojpeg()->save($point_file, 40);
                // $image->destroy();
            }

            // If the resize was successful, add it to the frames array.
            if (file_exists($point_file)) {
                $frames[] = $point_file;
            }
        }

        // If we have frames that were successfully extracted.
        if (! empty($frames)) {
            // We show each frame for 100 ms.
            $durations = array_fill(0, count($frames), 100);

            // Create a new GIF and save it.
            $gc = new GifCreator;
            $gc->create($frames, $durations, 0);
            file_put_contents($this->previewName, $gc->getGif());

            // Remove all the temporary frames.
            foreach ($frames as $file) {
                unlink($file);
            }
        }

        return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
        //        return null;
    }

    public function getPreviewFiles()
    {
        // utminfo(func_get_args());

        return Mediatag::$finder->Search(__INC_WEB_PREVIEW_DIR__ . '/' . __LIBRARY__, '*.gif');
    }
}
