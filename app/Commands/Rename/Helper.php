<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Utilities\Strings;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use Symfony\Component\Finder\Finder as SFinder;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;
use function is_array;

trait Helper
{
    public static $selfClass;

    public function renameVids($option = null)
    {
        // utminfo(func_get_args());
        // utmdd([__METHOD__, Mediatag::$SearchArray]);

        $file_array = (new MediaFinder)->search(__CURRENT_DIRECTORY__, '/Ph[a-z0-9]{8,}\.mp4$/');
        // utmdd($file_array);

        // foreach ($file_array as $key => $file) {
        //     $php = MediaFile::getVideoKey($file);

        //     $matched = preg_match('/(p?h?P?H?[a-z0-9]{8,})/', $php, $output_array);
        //     if ($matched == 0) {
        //         Mediatag::$Console->writeln($file);
        //     }
        //     // utmdd($matched, $output_array, $php);
        // }
        // exit;

        foreach ($file_array as $key => $file) {
            $oldName = $file;

            $fs        = new File($file);
            $videoData = $fs->get();
            $fileObj   = new fileReader($videoData);
            $filename  = $fileObj->getFilename($file);
            if ($filename !== null) {
                $file    = $filename;
                $newName = $file;
            }

            if (Option::isTrue('lowercase')) {
                $video_key = MediaFile::getVideoKey($file);
                preg_match('/(.*)(-p?h?[a-z0-9]{5,}).(.*)/i', $file, $output_array);
                $newName = $output_array[1] . '-' . $video_key . '.' . $output_array[3];
            }
            $newName    = $this->cleanFilename($newName);
            $backupName = str_replace('XXX', 'XXX/backup', $oldName);

            // utmdd([__METHOD__, $oldName, $newName]);
            if (! str_starts_with($oldName, __PLEX_HOME__)) {
                continue;
            }
            if (! str_starts_with($newName, __PLEX_HOME__)) {
                continue;
            }

            if ($newName == $oldName) {
                // Mediatag::$output->writeln('<comment> Skipping renaming file ' . $oldName . '</>');

                continue;
            }
            // utmdump($output_array, $video_key);
            // ;
            // Mediatag::$output->writeln('renaming file <comment> ' . $oldName . '</>');
            // Mediatag::$output->writeln('<comment> ' . $newName . '</>');
            // (new SfSystem)->copy($oldName, $backupName);

            $this->renameFile($oldName, $newName);
        }

        return 0;
    }

    public static function __callStatic($method, $args): string
    {
        // utminfo(func_get_args());

        //  $genre = str_replace('get', '', $method);
        return self::get($method, $args[0]['genre']);
    }

    public function getGenres($metadata)
    {
        // utminfo(func_get_args());

        $genreArray      = explode(',', $metadata['genre']);
        $this->genrePath = [];
        foreach ($genreArray as $genre) {
            $genre = str_replace(' ', '_', $genre);
            $genre = strtolower($genre);
            $res   = self::get($genre, $metadata['genre']);

            // $results[$genre] = $res;
            $this->genrePath[$genre] = $res;
        }

        return self::compare($this);
    }

    public static function get($genre, $arg)
    {
        // utminfo(func_get_args());
        $genre = strtolower($genre);
        $genre = str_replace('_', ' ', $genre);
        $arg   = strtolower($arg);

        if (str_contains($arg, $genre)) {
            return 1;
        }

        return 0;
    }

    public static function compare($object)
    {
        // utminfo(func_get_args());

        self::$selfClass = $object->genrePath;

        // $genreArray = [
        //     'mmf' => 1, 'mff' => 2, 'group' => 4,
        //        'orgy' => 8, 'Compilation' => 16, 'threesome' => 32, 'double_penetration' => 64, 'Single' => 128,
        // ];
        // $code = 0;
        // foreach($genreArray as $gen => $bit){
        //     if (self::istrue($gen) ) {
        //         $code = $code + $bit;
        //     }
        // }
        // utmdd([__METHOD__,$code]);

        if (
            (self::istrue('mmf') || self::istrue('double_penetration'))
            && ! self::istrue('group') && ! self::istrue('Compilation')
        ) {
            return 'MMF';
        }

        if (self::istrue('mff') && ! self::istrue('group') && ! self::istrue('Compilation')) {
            return 'MFF';
        }

        if (self::istrue('mff') && (self::istrue('mmf') || self::istrue('double_penetration')) && ! self::istrue('Compilation')) {
            return false;
            // return 'Threesome';
        }
        if (self::istrue('Threesome') && ! self::istrue('group') && ! self::istrue('Compilation') && ! self::istrue('Single')) {
            return false;
            // return 'Threesome';
        }

        if (self::istrue('Group') || self::istrue('orgy') && ! self::istrue('Compilation')) {
            return 'Group';
        }
        if (self::istrue('Compilation')) {
            return 'Compilation';
        }
        if (self::istrue('Single')) {
            return 'Single';
        }
        if (self::istrue('Bisexual')) {
            return 'Bisexual';
        }

        return false;
    }

    public static function istrue($var)
    {
        // utminfo(func_get_args());

        $var = strtolower($var);
        foreach (self::$selfClass as $genre => $value) {
            if ($var == $genre) {
                if ($value == '1') {
                    return true;
                }
            }
        }

        return false;
    }

    public function translate($text)
    {
        // utminfo(func_get_args());

        // $filename = Strings::translate($text);
        // $filename = $this->cleanFilename($filename);

        // $this->renameFile($text, $filename);
    }

    public function cleanFilename($file)
    {
        // utminfo(func_get_args());

        $file_name = basename($file);
        $file_dir  = dirname($file);
        $file_name = Strings::cleanFileName($file_name);

        return $file_dir . '/' . $file_name;
    }

    public function renameFile($oldName, $newName, $write = true)
    {
        // utminfo(func_get_args());

        $color       = 'comment';
        $rtn_message = '';

        if (file_exists($oldName)) {
            if ($oldName != $newName) {
                if (file_exists($newName)) {
                    $newName = str_replace('.mp4', '_1.mp4', $newName);
                    if (file_exists($newName)) {
                        $newName = str_replace('_1.mp4', '_2.mp4', $newName);
                    }
                }

                if (! Option::isTrue('test')) {
                    $color = 'fg=red';
                    utmdump([__METHOD__, $oldName, $newName]);

                    Filesystem::renameFile($oldName, $newName);
                    // } else {
                    // Mediatag::$output->writeln('<info> test </info>');
                }

                if ($write == true) {
                    $message = 'Renaming file from ' . PHP_EOL . '<' . $color . '>' . basename($oldName) . '</' . $color . '> to ' . PHP_EOL . '<' . $color . '>' . basename($newName) . '</' . $color . '> ';
                    Mediatag::$output->writeln('<info>' . $message . '</info>');
                } else {
                    $rtn_message = ['Renaming files', ['Old' => basename($oldName)], ['New' => basename($newName)]];
                }
            }

            return [$newName, $rtn_message];
        }
    }
}
