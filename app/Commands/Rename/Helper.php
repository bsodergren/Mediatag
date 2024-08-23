<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\TagReader;
use UTM\Utilities\Option;
use Mediatag\Utilities\Strings;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use Symfony\Component\Finder\Finder as SFinder;
use Symfony\Component\Process\Process as ExecProcess;

trait Helper
{
    public static $selfClass;

    public static function __callStatic($method, $args): string
    {
        utminfo();

        //  $genre = str_replace('get', '', $method);
        return self::get($method, $args[0]['genre']);
    }

    public function prunedirs()
    {
        utminfo();

        Filesystem::prunedirs();
    }

    public function moveStudios()
    {
        utminfo();

        $file_array = [];
        $tagConn    = new DbMap();

        if (Option::isTrue('filelist')) {
            $file         = Option::getValue('filelist', 1);
            $video_file   = realpath($file);

            if (!file_exists($video_file)) {
                utmdd([__METHOD__, 'File doesnt exist']);

                return false;
            }

            $file_array[] = $video_file;
        } else {
            $finder = new SFinder();
            $finder->files()->in(__CURRENT_DIRECTORY__)->sortByName();

            if (Option::isTrue('depth')) {
                $depth = '< ' . Option::getValue('depth', 1)[0];

                $finder->depth($depth);
            }

            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $video_file   = $file->getRealPath();
                    if (str_contains($video_file, '-temp-')) {
                        continue;
                    }

                    $file_array[] = $video_file;
                }
            }
        }

        if (0 == \count($file_array)) {
            utmdd([__METHOD__, 'no files']);
        }

        foreach ($file_array as $__ => $file) {
            $message                             = '';
            // $oldName                             = $file;
            // $newName                             = $this->cleanFilename($file);
            // [$file,$message]                     = $this->renameFile($oldName, $newName, false);

            $fs                                  = new File($file);
            $videoData                           = $fs->get();
            $videoData['msg']                    = $message;
            $videoArray[$videoData['video_key']] = $videoData;
        }
        $SortDir    = false;
        foreach ($videoArray as $k => $videoData) {
            $text       = [];
            $video_file = $videoData['video_file'];
            $message    = $videoData['msg'];
            $metatags   = (new TagReader())->loadVideo($videoData)->getMetaValues();
            if (!\is_array($message)) {
                $message = [];
                //     Mediatag::$Console->info($message[0],$message[1],$message[2]);
            } else {
                $tbsep  = new TableSeparator();
                $text[] = $tbsep;
            }

            $genrePath  = '';
            $studio     = '';
            $prefix     = '';
            if (\array_key_exists('studio', $metatags)) {
                $studio = $metatags['studio'];
            }
            // Mediatag::$output->writeln('Studio List -> <info>'.$studio.'</info>');

            if (Option::isTrue('genre')) {
                $genrePath = '/Sort';
                $SortDir   = true;

                $genDir    = $this->getGenres($metatags);
                if (false !== $genDir) {
                    $SortDir   = false;
                    $genrePath = '/' . $genDir;
                }
            }
            foreach (__SKIP_STUDIOS__ as $k) {
                //   $studio_dir = str_replace($k, '', $studio_dir);
                //    $studio_dir_arry['aray'][] =$studio_dir;
                // Mediatag::$output->writeln('Studio List -> <info>'.$studio.'</info>');

                // $studio = preg_replace('/\/?'.$k.'\b\/?/i', '', $studio);
            }
            // Mediatag::$output->writeln('Studio List -> <info>'.$studio.'</info>');

            if (Option::isTrue('studio')) {
                $prefix = '/' . Option::getValue('studio', 1)[0];
            }
            if (self::istrue('pov')) {
                //   $prefix = '/POV';
            }

            if ('' == $studio) {
                $studio_dir = 'Misc/';
            } else {
                $studios    = explode('/', $studio);
                $Arraykey   = array_key_last($studios);
                $studio_dir = $tagConn->getStudioPath($studios[$Arraykey]);
                if (false == $studio_dir) {
                    // continue;
                    $studio_dir = 'New/' . $studios[$Arraykey];
                }
            }

            $video_path = $studio_dir . $genrePath;
            if (true == $SortDir) {
                $video_path = 'Sort/' . $studio_dir;
            }

            $newPath    = __PLEX_HOME__ . '/' . __LIBRARY__ . '/' . $video_path;
            $dupePath   = __PLEX_HOME__ . '/Dupes/' . __LIBRARY__;
            $newPath    = nFileSystem::normalizePath($newPath);
            $dupePath   = nFileSystem::normalizePath($dupePath);
            if (!is_dir($newPath)) {
                if (!Option::isTrue('test')) {
                    nFileSystem::createDir($newPath, 0755);
                }
            }
            if (true == $SortDir) {
                foreach (['Group', 'MMF', 'MFF', 'Single', 'Compilation'] as $geneDir) {
                    $gebrePath = $newPath . '/' . $geneDir;
                    if (!is_dir($gebrePath)) {
                        if (!Option::isTrue('test')) {
                            nFileSystem::createDir($gebrePath, 0755);
                        }
                    }
                }
            }

            if (!is_dir($dupePath)) {
                // Mediatag::$output->writeln("Creating {$studio_dir}{$genrePath}");
                if (!Option::isTrue('test')) {
                    nFileSystem::createDir($dupePath, 0755);
                }
            }

            $video_name = basename($video_file);
            $newFile    = $newPath . '/' . $video_name;
            $dupeFile   = $dupePath . '/' . $video_name;

            if ($newFile == $video_file) {
                //  Mediatag::$output->writeln('Nothing to rename ');

                continue;
            }
            // /*
            if (!file_exists($newFile)) {
                $text[] = 'Moving File';

                //  utmdd([__METHOD__,$video_file,$newFile]);
                if (Option::isTrue('genre')) {
                    $text[] = ['Genre List' => $metatags['genre']];
                }

                $text[] = ['Moving' => $video_name];
                if (!Option::isTrue('test')) {
                    (new SfSystem())->rename($video_file, $newFile, false);
                    $text[]  = ['New Path' => $video_path];

                    $infoMsg = array_merge($message, $text);

                    Mediatag::$Console->info(...$infoMsg);
                }
            } else {
                if (!Option::isTrue('test')) {
                    (new SfSystem())->rename($video_file, $dupeFile, true);
                }
                Mediatag::$Console->info('Duplicate', ['Video' => $video_name]);
            }
            // */
        }
    }

    public function getGenres($metadata)
    {
        utminfo();

        $genreArray      = explode(',', $metadata['genre']);
        $this->genrePath = [];
        foreach ($genreArray as $genre) {
            $genre                   = str_replace(' ', '_', $genre);
            $genre                   = strtolower($genre);
            $res                     = self::$genre($metadata);

            // $results[$genre] = $res;
            $this->genrePath[$genre] = $res;
        }

        return self::compare($this);
    }

    public static function get($genre, $arg)
    {
        utminfo();

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
        utminfo();

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

        if ((self::istrue('mmf') || self::istrue('double_penetration'))
        && !self::istrue('group') && !self::istrue('Compilation')) {
            return 'MMF';
        }

        if (self::istrue('mff') && !self::istrue('group') && !self::istrue('Compilation')) {
            return 'MFF';
        }

        if (self::istrue('mff') && (self::istrue('mmf') || self::istrue('double_penetration')) && !self::istrue('Compilation')) {
            return false;
            // return 'Threesome';
        }
        if (self::istrue('Threesome') && !self::istrue('group') && !self::istrue('Compilation') && !self::istrue('Single')) {
            return false;
            // return 'Threesome';
        }

        if (self::istrue('Group') || self::istrue('orgy') && !self::istrue('Compilation')) {
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
        utminfo();

        $var = strtolower($var);
        foreach (self::$selfClass as $genre => $value) {
            if ($var == $genre) {
                if ('1' == $value) {
                    return true;
                }
            }
        }

        return false;
    }

    public function translate($text)
    {
        utminfo();

        // $filename = Strings::translate($text);
        // $filename = $this->cleanFilename($filename);

        // $this->renameFile($text, $filename);
    }

    public function rename($option = null)
    {
        utminfo();

        foreach (Mediatag::$SearchArray as $key => $file) {
            $oldName   = $file;

            $fs        = new File($file);
            $videoData = $fs->get();
            $fileObj   = new fileReader($videoData);
            $file      = $fileObj->getFilename($file);
            // utmdd([__METHOD__,$file]);

            $newName   = $this->cleanFilename($file);
            $this->renameFile($oldName, $newName);
        }

        return 0;
    }

    public function cleanFilename($file)
    {
        utminfo();

        $file_name = basename($file);
        $file_dir  = \dirname($file);
        $file_name = Strings::cleanFileName($file_name);

        return $file_dir . '/' . $file_name;
    }

    public function renameFile($oldName, $newName, $write = true)
    {
        utminfo();

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

                if (!Option::isTrue('test')) {
                    $color = 'fg=red';

                    Filesystem::renameFile($oldName, $newName);
                }
                if (true == $write) {
                    $message = 'Renaming file from <' . $color . '>' . basename($oldName) . '</' . $color . '> to <' . $color . '>' . basename($newName) . '</' . $color . '> ';
                    Mediatag::$output->writeln('<info>' . $message . '</info>');
                } else {
                    $rtn_message = ['Renaming files', ['Old' => basename($oldName)], ['New' => basename($newName)]];
                }
            }

            return [$newName, $rtn_message];
        }
    }
}
