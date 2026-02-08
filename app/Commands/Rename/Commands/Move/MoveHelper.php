<?php

namespace Mediatag\Commands\Rename\Commands\Move;

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

trait MoveHelper
{
    public function prunedirs()
    {
        // utminfo(func_get_args());

        Filesystem::prunedirs();
    }

    public function moveStudios()
    {
        // utminfo(func_get_args());

        // utmdd('fasdfsd', $this->VideoList);

        $file_array = [];
        $tagConn    = new DbMap;

        if (Option::isTrue('filelist')) {
            $file       = Option::getValue('filelist', 1);
            $video_file = realpath($file);

            if (! file_exists($video_file)) {
                utmdd([__METHOD__, 'File doesnt exist']);

                return false;
            }

            $file_array[] = $video_file;
        } else {
            $finder = new SFinder;
            $finder->files()->in(__CURRENT_DIRECTORY__)->sortByName();

            if (Option::isTrue('depth')) {
                $depth = '< ' . Option::getValue('depth', 1)[0];

                $finder->depth($depth);
            }

            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $video_file = $file->getRealPath();
                    if (str_contains($video_file, '-temp-')) {
                        continue;
                    }

                    $file_array[] = $video_file;
                }
            }
        }

        if (count($file_array) == 0) {
            utmdd([__METHOD__, 'no files']);
        }
        // $progressBar = new ProgressBar(Mediatag::$Display->BarSection1, \count($file_array));
        // $progressBar->setBarWidth(__CONSOLE_WIDTH__ - 50);
        // $progressBar->start();

        foreach ($file_array as $__ => $file) {
            $message = '';
            // $oldName                             = $file;
            // $newName                             = $this->cleanFilename($file);
            // [$file,$message]                     = $this->renameFile($oldName, $newName, false);

            $fs                                  = new File($file);
            $videoData                           = $fs->get();
            $videoData['msg']                    = $message;
            $videoArray[$videoData['video_key']] = $videoData;
        }

        $SortDir = false;
        foreach ($videoArray as $k => $videoData) {
            // $progressBar->advance();
            $text       = [];
            $video_file = $videoData['video_file'];
            $message    = $videoData['msg'];
            $metatags   = (new TagReader)->loadVideo($videoData)->getMetaValues();

            if (! is_array($message)) {
                $message = [];
                //     Mediatag::$Console->info($message[0],$message[1],$message[2]);
            } else {
                $tbsep  = new TableSeparator;
                $text[] = $tbsep;
            }

            $genrePath = '';
            $studio    = '';
            $prefix    = '';
            if (array_key_exists('studio', $metatags)) {
                $studio = $metatags['studio'];
            }
            // Mediatag::$output->writeln('Studio List -> <info>'.$studio.'</info>');
            if (Option::isTrue('genre')) {
                $genrePath = '/Sort';
                $SortDir   = true;

                $genDir = $this->getGenres($metatags);
                if ($genDir !== false) {
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

            if ($studio == '') {
                $studio_dir = 'Misc/';
            } else {
                $studios  = explode('/', $studio);
                $Arraykey = array_key_first($studios);

                $studio_dir = $tagConn->getStudioPath($studios[$Arraykey]);
                // utmdd($studio_dir);

                if ($studio_dir == false) {
                    $Arraykey   = array_key_last($studios);
                    $studio_dir = $tagConn->getStudioPath($studios[$Arraykey]);

                    if ($studio_dir == false) {
                        // continue;
                        $studio_dir = 'New/' . $studios[$Arraykey];
                    }
                }
            }
            // utmdd($studio_dir);

            $video_path = $studio_dir . $genrePath;
            if ($SortDir == true) {
                $video_path = 'Sort/' . $studio_dir;
            }
            $newPath = __PLEX_HOME__ . '/' . __LIBRARY__ . '/' . $video_path;
            $newPath = str_replace(__LIBRARY__ . '/' . __LIBRARY__ . '/', __LIBRARY__ . '/', $newPath);

            $newPath = nFileSystem::normalizePath($newPath);
            // utmdd([$newPath]);

            if (! is_dir($newPath)) {
                if (! Option::isTrue('test')) {
                    nFileSystem::createDir($newPath, 0755);
                }
            }
            if ($SortDir == true) {
                foreach (__GENRE_LIST__ as $geneDir) {
                    $gebrePath = $newPath . '/' . $geneDir;
                    if (! is_dir($gebrePath)) {
                        if (! Option::isTrue('test')) {
                            nFileSystem::createDir($gebrePath, 0755);
                        }
                    }
                }
            }

            $video_name = basename($video_file);
            $newFile    = $newPath . '/' . $video_name;

            if ($newFile == $video_file) {
                //  Mediatag::$output->writeln('Nothing to rename ');

                continue;
            }
            // /*
            if (! file_exists($newFile)) {
                $text[] = 'Moving File';

                if (Option::isTrue('genre')) {
                    $text[] = ['Genre List' => $metatags['genre']];
                }

                $text[] = ['Moving' => File::videoPath($video_name)];
                Mediatag::$output->writeln('Renaming <file>' . File::videoPath($video_file) . '</>' . PHP_EOL . ' <comment>' . File::videoPath($newFile) . '</>');

                if (! Option::isTrue('test')) {
                    (new SfSystem)->rename($video_file, $newFile, false);
                } else {
                }
                $text[] = ['New Path' => $video_path];

                $infoMsg = array_merge($message, $text);
                // Mediatag::$Console->table($infoMsg);
            } else {
                if (! Option::isTrue('test')) {
                    [$newFile, $video_file] = VideoFileInfo::compareDupes($newFile, $video_file);

                    $dupePath = __PLEX_HOME__ . '/Dupes/' . __LIBRARY__ . '/' . $video_path;

                    $dupePath = nFileSystem::normalizePath($dupePath);
                    $dupeFile = $dupePath . '/' . $video_name;

                    if (! is_dir($dupePath)) {
                        Mediatag::$output->writeln('Creating <file> ' . $studio_dir . '</> ' . PHP_EOL . '<comment>' . $genrePath . '</>');
                        if (! Option::isTrue('test')) {
                            nFileSystem::createDir($dupePath, 0755);
                        }
                    }

                    // if (!file_exists($newFile)) {
                    Mediatag::$output->writeln('Renaming duplicate ' . PHP_EOL . '<file>' . File::videoPath($video_file) . '</> ' . PHP_EOL . '<comment> ' . File::videoPath($dupeFile) . '</>');
                    if (! Option::isTrue('test')) {
                        (new SfSystem)->rename($video_file, $dupeFile, true);
                    }

                    // if (!file_exists($video_file)) {
                    //     Mediatag::$output->writeln('Renaming <file>'.__LINE__.File::videoPath($newFile).' </> '.PHP_EOL.'<comment>'.File::videoPath($video_file).'</>');
                    //     if (!Option::isTrue('test')) {
                    //        (new SfSystem())->rename($newFile, $video_file, false);
                    //     }
                    // }

                    // utmdd([$video_file, $newFile, $dupeFile]);
                }
                // Mediatag::$output->writeln($video_name.' is dup');
                // Mediatag::$Console->error('Duplicate Video '.$video_name);
            }
            // */
        }
    }
}
