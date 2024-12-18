<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Commands\Test\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Modules\VideoData\Duration;
use Mediatag\Traits\ffmpeg;

trait Helper
{
    use ffmpeg;

    use MarkerHelper;

    public $Marker;

    public function createClip()
    {
        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $this->Marker = new Markers();
            $this->Marker->getvideoId($key);
            if (null !== $this->Marker->video_id) {
                $query = $this->Marker->videoQuery($this->Marker->video_id);

                $result = Mediatag::$dbconn->query($query);

                $markers = $this->getVideoMarks($result);
                //  utmdd($markers);

                // utmdump([$this->Marker->video_id,$query,$markers ]);
                if (\count($markers) > 0) {
                    $markerArray[] = $markers;
                }
            }
        }

        foreach ($markerArray as $i =>$fileRow) {
            foreach ($fileRow as $K =>$FILE) {
                $filename = $FILE['filename'];
                if (\count($FILE['markers']) > 0) {
                    foreach ($FILE['markers'] as $idx =>$marker) {
                        $outputFile = str_replace('/XXX', '/Clips', $filename);
                        $outputFile = str_replace('.mp4', '_'.$marker['text'].'_'.$idx.'.mp4', $outputFile);
                        Filesystem::createDir(\dirname($outputFile));
                        $this->ffmpegCreateClip($filename, $marker['start'], $marker['end'], $outputFile);
                        // utmdump([$filename, $marker['start'], $marker['end'], $outputFile]);
                    }
                }
            }
        }

        // exit;
    }

    public function convert()
    {
        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $video_file = $vidArray['video_file'];
            $video_path = $vidArray['video_path'];
            $video_name = $vidArray['video_name'];
            Mediatag::$output->writeln('<info>Transcoding Video '.$video_name.'</info>');

            $pcs = explode('.', $video_name);
            Filesystem::createDir($video_path.\DIRECTORY_SEPARATOR.'mp4');

            $new_file = $video_path.\DIRECTORY_SEPARATOR.'mp4'.\DIRECTORY_SEPARATOR.$pcs[0].'.mp4';
            $this->convertVideo($video_file, $new_file);
        }
        exit;
    }

    public function t1($val, $min, $max)
    {
        // utminfo(func_get_args());

        return $val >= $min && $val < $max;
    }

    public function sortFiles()
    {
        // utminfo(func_get_args());

        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $min     = 0;
            $hours   = 0;
            $minutes = 0;
            $seconds = 0;

            $this->duration = new Duration(Mediatag::$input, Mediatag::$output);
            $duration       = $this->duration->getDbDuration($vidArray['video_key'], $vidArray['video_file']);
            $duration       = (int) $duration;

            $seconds = round($duration / 1000);
            $hours   = floor($seconds / 3600);

            $min = round((float) $seconds / 60 % 60);

            $sec     = round($seconds % 60);
            $minutes = $min + ($hours * 60);

            $dur = $minutes;
            if ($this->t1($minutes, 0, 10)) {
                $video_array['0_9'][] = $vidArray['video_file'];
            }
            if ($this->t1($minutes, 10, 20)) {
                $video_array['10_20'][] = $vidArray['video_file'];
            }
            if ($this->t1($minutes, 20, 30)) {
                $video_array['20_30'][] = $vidArray['video_file'];
            }
            if ($this->t1($minutes, 30, 40)) {
                $video_array['30_40'][] = $vidArray['video_file'];
            }
            if ($this->t1($minutes, 40, 50)) {
                $video_array['40_50'][] = $vidArray['video_file'];
            }
            if ($this->t1($minutes, 50, 60)) {
                $video_array['50_60'][] = $vidArray['video_file'];
            }
            if (60 <= $minutes) {
                $video_array['60'][] = $vidArray['video_file'];
            }

            // printf('%02d:%02d:%02d', $hours, $minutes, $sec);
        }

        $this->symlinkFiles($video_array);
    }

    public function symlinkFiles($video_array)
    {
        // utminfo(func_get_args());

        $filesystem = new Filesystem();

        foreach ($video_array as $dir => $fileArray) {
            $new_path = __PLEX_HOME__.'/Duration/'.$dir;
            if (!is_dir($new_path)) {
                $filesystem->mkdir($new_path);
            }
            foreach ($fileArray as $file) {
                $new_filePath = str_replace(__PLEX_HOME__.'/'.__LIBRARY__, $new_path, $file);
                if (!file_exists($new_filePath)) {
                    $filesystem->symlink($file, $new_filePath);
                    echo 'creating symlink for '.basename($file)."\n";
                    // utmdd([__METHOD__,$new_filePath, $file]);
                }
            }
        }
    }

    public function mvFiles($video_array)
    {
        // utminfo(func_get_args());

        $filesystem = new Filesystem();
        // foreach ($video_array as $dir => $fileArray)
        // {
        $new_path = __PLEX_HOME__.'/backup_ph';
        foreach ($video_array as $file) {
            $file         = trim($file);
            $new_fileName = str_replace(__PLEX_HOME__.'/'.__LIBRARY__.'/Pornhub', $new_path, $file);
            $new_fileName = str_replace(__PLEX_HOME__.'/'.__LIBRARY__.'/Studios', $new_path, $new_fileName);

            //                $new_fileName = str_replace($new_path.'/Studios', $new_path, $new_fileName);
            $new_filePath = str_replace(basename($new_fileName), '', $new_fileName);
            if (!is_dir($new_filePath)) {
                $filesystem->mkdir($new_filePath);
            }

            if (file_exists($file)) {
                if (!file_exists($new_fileName)) {
                    $filesystem->rename($file, $new_fileName);
                    echo 'mving for '.basename($file)."\n";
                    //   utmdd([__METHOD__,$new_fileName, $file]);
                }
            }
        }
        //  utmdd([__METHOD__,$dirs]);
        // }
    }

    public function getPhKeys()
    {
        // utminfo(func_get_args());

        $ph_video    = [];
        $video_array = [];
        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $filename = basename($vidArray['video_file']);
            //            if(str_contains($filename,$dir)){
            if (!str_starts_with($key, 'x')) {
                $ph_video[] = 'https://www.pornhub.com/view_video.php?viewkey='.$key.\PHP_EOL;
                echo 'adding '.basename($vidArray['video_file'])."\n";
                $video_array[] = $vidArray['video_file'].\PHP_EOL;
            }
        }
        file_put_contents(__LIBRARY__.'_playlist.txt', $ph_video);
        file_put_contents(__LIBRARY__.'files.txt', $video_array);

        $this->mvFiles($video_array);
    }
}
