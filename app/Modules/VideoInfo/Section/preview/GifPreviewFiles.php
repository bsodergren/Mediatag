<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section\preview;

use FFMpeg\FFProbe;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\VideoPreview;
// use Intervention\Image\Image;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Utilities\GifCreator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Helper\ProgressBar;

class GifPreviewFiles extends VideoPreview implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    use MediaFFmpeg;
    public $videoRange  = 80;
    public $videoSlides = 15;

    public function build_video_thumbnail()
    {
        // Create a temp directory for building.
        $temp    = __PLEX_VAR_DIR__.'/build/'.md5($this->video_file);
        $options = [
            'temporary_directory' => $temp,
            'loglevel'            => 'quiet',
             'ffmpeg.binaries'  => '/home/bjorn/bin/ffmpeg',
                'ffprobe.binaries' => '/home/bjorn/bin/ffprobe'
        ];
        (new Filesystem())->mkdir($temp);

        // Use FFProbe to get the duration of the video.
        $ffprobe = FFProbe::create($options, $this->logger);

        $duration = floor($ffprobe
            ->format($this->video_file)
            ->get('duration'));

        // If we couldn't get the direction or it was zero, exit.
        if (empty($duration)) {
            return null;
        }

        $videoRange  = $this->videoRange;
        $videoSlides = $this->videoSlides;
        $points      = array_map(function ($n) { return round($n, 0); }, range(1, $videoRange, $videoRange / $videoSlides));

        $frames      = [];
        $progressBar = new ProgressBar(Mediatag::$output, \count($points));

        $progressBar->setFormat('<comment>%no:4s%</comment> <fg=red>Writing Preview</>  <info>%message%</info> <fg=cyan;options=bold>[%bar%]</> %percent:3s%%');
        $progressBar->setMessage($this->fileCount--, 'no');

        $message = $this->setMessage($this->video_file);

        $progressBar->setMessage($message, 'message');        // $progressBar->setBarWidth("100");

        $progressBar->start();
        foreach ($points as $point) {
            $time_secs = floor($duration * ($point / 100));
            $progressBar->advance();

            $point_file = "$temp/$point.jpg";

            $this->ffmegCreateThumb($this->video_file, $point_file, $time_secs);

            if (file_exists($point_file)) {
                $frames[] = $point_file;
            }
        }
        $progressBar->finish();
        Mediatag::$output->writeln('');

        if (!empty($frames)) {
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
    }
}
