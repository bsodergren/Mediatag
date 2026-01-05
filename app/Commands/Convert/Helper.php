<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert;

use const PHP_EOL;
use FFMpeg\FFMpeg;

use function count;
use function is_array;
// use Mediatag\Traits\CaseHelper;
use Nette\Utils\Callback;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use FFMpeg\Format\Video\X264;
use Mediatag\Utilities\Chooser;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Filters\Video\ResizeFilter;
use Symfony\Component\Process\Process;

use Mediatag\Modules\Executable\WriteMeta;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Modules\Filesystem\MediaFinder;
use Symfony\Component\HttpClient\HttpClient;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;

trait Helper
{


    public function ConvertFiles()
    {
        foreach ($this->file_array as $file) {
            $this->convertMedia($file);
            // utmdd("fsda");
        }
    }

    public function convertMedia($video_file)
    {

        $mp4_file = basename($video_file, '.' . strtoupper($this->fileExtension));
        $mp4_file = basename($mp4_file, '.' . $this->fileExtension) . '.mp4';
        $mp4_dir  = dirname($video_file, 1) . DIRECTORY_SEPARATOR . 'mp4' . DIRECTORY_SEPARATOR;

        $moved_file = dirname($video_file, 1) . DIRECTORY_SEPARATOR . 'moved' . DIRECTORY_SEPARATOR;

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
                MediaFilesystem::renameFile($video_file, $moved_file);
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
        //utmdd($format->listeners());
        $format->on('progress', function ($advancedMedia, $format, $percentage) {
            Mediatag::$Display->BarSection2->overwrite("<info>$percentage % transcoded</info>");
            if ($percentage >= '99') {
                Mediatag::$output->writeln('<info>finished</info>');
            } else {

            }
            // Mediatag::$output->write('<info>Info compilation called  ' . $percentage . ' </info>');
            // utmdump("$percentage % transcoded");
        });
        $format->on('finish', function ($advancedMedia, $format, $percentage) {
            // Mediatag::$Display->BarSection2->overwrite("<info>$percentage % transcoded</info>");
            Mediatag::$output->writeln('<info>Info compilation called  ' . $percentage . ' </info>');
            // utmdump("$percentage % transcoded");
        });
        // $format
        //     ->setKiloBitrate(1000)
        //     ->setAudioChannels(2)
        //     ->setAudioKiloBitrate(256);

        // $video->filters()->resize(new Dimension(320, 240), ResizeFilter::RESIZEMODE_FIT, true);
        $video->save($format, $mp4_file);

        MediaFilesystem::renameFile($video_file, $moved_file);

    }

}
