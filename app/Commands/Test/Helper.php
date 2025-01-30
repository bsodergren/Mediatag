<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Duration;
use UTM\Utilities\Option;

trait Helper
{
    use HelperCmds;

    public function mvOldFiles()
    {
        $sql = "SELECT *  FROM mediatag_video_file WHERE `video_key` IN ('64c3c368aa608',\n"

        ."'64f5b13110be6',\n"
        ."'64713464ad036',\n"
        ."'ph5ba35f316a27a',\n"
        ."'ph5c0fa1de97f01',\n"
        ."'ph5c052e62d4a23',\n"
        ."'ph5ca5f1441df6b',\n"
        ."'ph5cde966665e7c',\n"
        ."'ph5d44120f3ea6b',\n"
        ."'ph5d52825f400c3',\n"
        ."'ph5e01e8fe95407',\n"
        ."'ph5e468b9a55f04',\n"
        ."'ph5ec16e29d7dd5',\n"
        ."'ph5ecf5425b6aa4',\n"
        ."'ph5ed9db5918dc0',\n"
        ."'ph5efdb5ac8f26c',\n"
        ."'ph5f31eefb1525e',\n"
        ."'ph5f51e462bebd6',\n"
        ."'ph5fc0f2bf66e98',\n"
        ."'ph59f82fd31e4d2',\n"
        ."'ph59f83b0d745b1',\n"
        ."'ph59f832451c4ca',\n"
        ."'ph60d02a7da57d2',\n"
        ."'ph62b04e41183d0',\n"
        ."'ph62c6d20fbf254',\n"
        ."'ph62dad25479d07',\n"
        ."'ph605f88c08e961',\n"
        ."'ph617bbb9baa501',\n"
        ."'ph5703a540748d8',\n"
        ."'ph5703bf1661e26',\n"
        ."'ph6128c02330165',\n"
        ."'ph61160ade8fd6b',\n"
        ."'ph611609b41034e') ORDER BY `video_key` DESC;";

        $result = Mediatag::$dbconn->query($sql);
        foreach ($result as $row) {
            $filename = $row['fullpath'].\DIRECTORY_SEPARATOR.$row['filename'];
            if (file_exists($filename)) {
                $new_path = str_replace('/XXX/Pornhub', '/XXX/OldPH', $row['fullpath']);
                (new Filesystem())->mkdir($new_path);
                $new_name = $new_path.\DIRECTORY_SEPARATOR.$row['filename'];

                (new Filesystem())->rename($filename, $new_name, true);
            }
            // utmdd($filename,$new_name);
        }
    }

    public function colors()
    {
        $colors = [
            'black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white', 'gray',
            'bright-red', 'bright-green', 'bright-yellow', 'bright-blue', 'bright-magenta', 'bright-cyan', 'bright-white',
        ];

        foreach ($colors as $color) {
            $text = '<fg='.$color.'>'.$color.'</>';
            $text .= ' <fg='.$color.';options=bold> bold '.$color.'</>';
            $text .= ' <fg='.$color.';options=underscore> underscore '.$color.'</>';
            $text .= ' <fg='.$color.';options=blink> blink '.$color.'</>';
            $text .= ' <fg='.$color.';options=reverse> Reverse '.$color.'</>';

            Mediatag::$output->writeln($text);
        }

        return true;
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

    public function execCmd()
    {
        $fileList = $this->VideoList['file'];

        foreach ($fileList as $key => $file) {
            $this->videoFile[] = $file['video_file'];
        }
        $method = Option::getValue('cmd');
        $this->$method();
    }
}
