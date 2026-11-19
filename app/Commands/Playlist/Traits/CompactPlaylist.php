<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist\Traits;

use Mediatag\Commands\Playlist\Traits\PlaylistIds;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Mediatag\Utilities\MediaArray;
use Nette\Utils\Strings;
use UTM\Utilities\Option;

use function array_slice;
use function count;
use function in_array;

use const DIRECTORY_SEPARATOR;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const SORT_STRING;

trait CompactPlaylist
{
    public function compactPlaylist($line)
    {
        // $line .= "f";
        // return $line;
        if (str_contains($line, '&')) {
            $line = Strings::before($line, '&');

        }
        $ph_id = Strings::after($line, '=');


        if ($ph_id === null) {
            $ph_id = Strings::after($line, 'watch/');
            if ($ph_id !== null) {
                $ph_id = Strings::before($ph_id, '/');
            }
        }
        $ph_id = trim($ph_id);
        // utmdd([$ph_id=>in_array($ph_id, $this->ids, true)]);
        if (\in_array($ph_id, $this->ids, true) === false) {

            if (str_contains($line, 'view_video.php')) {
                return $line;
            }
            if (str_contains($line, 'watch')) {
                return $line;
            }
            if (str_contains($line, 'video-')) {
                return $line;
            }
        }

        return false;
    }

    public function docompactPlaylist($firstRun = false)
    {
        if (Option::istrue('url')) {
            return '';
        }

        // utmdd(__METHOD__);

        // utmdump($firstRun);
        // utminfo(func_get_args());
        //   Mediatag::debug('Compact Playlist', var_dump($firstRun));
        if ($this->secondRun === false) {
            if ($firstRun !== true) {
                return '';
            }
        }

        if (! Option::istrue('skip')) {
            $this->ids = $this->getDownloadedIds();
            if (! file_exists($this->playlist)) {
                Mediatag::$output->writeln('<info>File doesnt exist</info>');
                exit;
            }

            $f         = file($this->playlist, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);

            $before    = \count($f);

            $idCnt     = \count($this->ids);

            // utmdd(["Running Compact on " . $this->playlist, "playlist has " . $before . " videos",
            //     "kid List has " . $idCnt . " videos"]);

            if ($before > 0) {
                foreach ($f as $line) {
                    $array[] = $this->compactPlaylist($line);
                }
                // utmdd($array);
                $array         = Filesystem::readLines($this->playlist, [$this, 'compactPlaylist']);

                if (\count($array) > 0) {
                    $array = MediaArray::array_iunique($array);
                }
                $after         = \count($array);
                $trimmedLines  = $before - $after;

                $Video_Removed = 'Videos have';
                if ($trimmedLines == 1) {
                    $Video_Removed = 'Video has';
                }
                $Videos_left   = 'Videos';
                if ($after == 1) {
                    $Videos_left = 'Video';
                }

                $text          = '<comment> Before ' . $before . ', trimmeed ' . $trimmedLines . '</comment>';
                $text .= '<info> ' . $Video_Removed . ' been removed. There are now</info>';
                $text .= ' <comment>' . $after . '</comment> <info>' . $Videos_left . ' left</info>';

                Mediatag::$output->writeln($text);
                Filesystem::writePlaylist($this->playlist, $array);

                if ($after == 0) {
                    Mediatag::$output->writeln('<info> All files downloaded</info>');
                    Filesystem::delete($this->playlist);
                    exit;
                }
            }
        }

        if (Option::istrue('download')) {
            $this->dodownloadPlaylist();
        }
    }
}
