<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Subtitles;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Utilities\MediaArray;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function dirname;

trait SubtitlesHelper
{
    public function subtitlesMethod()
    {
        $this->storagedb = new StorageDB;
        if (Option::isTrue('update')) {
            $this->findMissing();

            return false;
        }

        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.vtt', exit: false);

        if (! empty($file_array)) {
            // utminfo(func_get_args());
            foreach ($file_array as $file) {
                $videoFile = $this->vtt2mp4($file);
                if ($videoFile === null) {
                    continue;
                }
                $this->storagedb->init($videoFile);
                $data = [
                    'subtitle' => 1,
                    'studio'   => 'Porn World',
                ];
                $where = ['video_key' => $this->storagedb->video_key];
                Mediatag::$dbconn->update($data, $where, __MYSQL_VIDEO_INFO__);
            }
        }
    }

    private function vtt2mp4($vtt)
    {
        $file = str_replace('.en.vtt', '.mp4', $vtt);
        $file = str_replace('/Subtitles', '', $file);

        if (is_file($file)) {
            return $file;
        }

        return null;
    }

    public function findMissing()
    {
        $file_array = Mediatag::$SearchArray;
        $vtt_array  = Mediatag::$finder->Search(__PLEX_HOME__ . DIRECTORY_SEPARATOR . 'Subtitles', '*.vtt', exit: false);
        $srt_array  = Mediatag::$finder->Search(__PLEX_HOME__ . DIRECTORY_SEPARATOR . 'Subtitles', '*.srt', exit: false);
        $captions   = array_merge($vtt_array, $srt_array);
        foreach ($file_array as $k => $file) {
            $fileinfo = (new MediaFile($file));
            $data     = [
                'video_key' => [$fileinfo->videokey(), '='],
            ];
            $output = Mediatag::$dbconn->getValue($data, 'subtitle', __MYSQL_VIDEO_INFO__);

            // Mediatag::$output->writeln($output);
            if ($output != 1) {
                preg_match('/([GP0-9]+)|(.*)_[HDP1080.mp4]+/', $fileinfo->filename(), $output_array);
                if (array_key_exists(2, $output_array)) {
                    $video_base = $output_array[2];
                } else {
                    $video_base = $output_array[1];
                }
                $matched = MediaArray::search($captions, $video_base);
                if ($matched == true) {
                    foreach ($matched as $capFile) {
                        $capfileInfo = pathinfo($capFile);
                        $extension   = '.en.' . $capfileInfo['extension'];

                        $subtitlePath = $fileinfo->filepath() . DIRECTORY_SEPARATOR . 'Subtitles';

                        $SubtitleFileName = str_replace(
                            '.mp4',
                            $extension,
                            $subtitlePath . DIRECTORY_SEPARATOR . $fileinfo->filename(),
                        );

                        if ($capfileInfo['extension'] == 'srt') {
                            $subtitleVTTFilename = str_replace('srt', 'vtt', $SubtitleFileName);
                            if (! file_exists($SubtitleFileName)) {
                                $original = file_get_contents($SubtitleFileName);
                                $vtt      = 'WEBVTT' . PHP_EOL . PHP_EOL . $original;
                                // Replace microseconds separator: 00,000 -> 00.000
                                $vtt = preg_replace('#(\d{2}),(\d{3})#', '${1}.${2}', $vtt);

                                // Write the .vtt file

                                file_put_contents($subtitleVTTFilename, $vtt);

                                // utmdd($SubtitleFileName,$subtitleVTTFilename);
                                // unlink($SubtitleFileName);
                                $SubtitleFileName = $subtitleVTTFilename;
                            }
                        }
                        if (! file_exists($SubtitleFileName)) {
                            Mediatag::$output->writeln($SubtitleFileName);
                            (new SfSystem)->rename($capFile, $SubtitleFileName, true);
                            // utmdd($SubtitleFileName);

                            // $captionFile = basename($capFile);
                            // $captionDir  = dirname($capFile);

                            // Mediatag::$output->writeln($fileinfo->filepath().'/'.$fileinfo->filename());
                            Mediatag::$output->writeln($SubtitleFileName);
                        }
                    }
                }
            }
        }
    }
}
