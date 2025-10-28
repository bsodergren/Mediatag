<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Sort;

use const DIRECTORY_SEPARATOR;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Utilities\Chooser;
use UTM\Utilities\Option;

use function count;
use function dirname;

trait Helper
{
    public static $selfClass;

    public function sortFiles()
    {
        if (count($this->file_array) == 0) {
            return false;
        }

        $idx = count($this->file_array);

        foreach ($this->file_array as $file) {
            $basePath = dirname($file);
            $nextDir  = basename($basePath);
            // // utmdump($nextDir);
            $filename = basename($file);

            // $success = preg_match('/.*(group|mmf|dp|mff|single|only girls|trans|blowjob|only blowjobs|compilation|bisexual|feature|hotwife).*/i', $filename, $matches);
            // if (true == $success) {
            //     $this->genre = $matches[1];
            //     //  $genre = $matches[1];
            // }
            // utmdd($matches);

            Chooser::FormatQuestion('<info>%text%</info>');

            $qText = [
                'text'     => '<comment>%idx%</comment>) Move <download>%filename%</download> to new Genre?',
                'filename' => $filename,
                'idx'      => $idx];
            $idx--;
            $newGenre = Chooser::AskQuestion($qText, $this->genreDirs, 'Exit');

            // utmdd($newGenre);
            if ($newGenre === false) {
                Mediatag::$output->writeln('Exiting');

                return false;
                // break;
            }
            $newFilename = $basePath . DIRECTORY_SEPARATOR . $newGenre . DIRECTORY_SEPARATOR . $filename;
            Mediatag::$output->writeln('Moved ' . $filename . ' to <info>' . $newGenre . '</info>');
            $this->renameFile($file, $newFilename, false);
        }

        return true;
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
                    Filesystem::renameFile($oldName, $newName);
                } else {
                    $color = 'fg=red';
                    $write = true;
                }

                if ($write == true) {
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
