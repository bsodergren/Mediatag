<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

use const DIRECTORY_SEPARATOR;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const PHP_EOL;
use const SORT_STRING;

// use Nette\Utils\FileSystem as NetteFile;
use Mediatag\Commands\Playlist\Traits\PlaylistIds;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Nette\Utils\Strings;
use UTM\Utilities\Option;

use function array_key_exists;
use function array_slice;
use function count;
use function in_array;
use function is_array;

trait Helper
{
    use PlaylistIds;

    public $url = 'https://www.pornhub.com/playlist/watchlater';

    public $idList = [];

    public $DownloadableIds = [];

    private $secondRun = false;

    public function youtubeWatchPlaylist()
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('<info> downloading the watchlater list </info>');

        if (Option::istrue('url')) {
            $this->playlist_url = Option::getValue('url');
        }

        $this->youtube->run($this->playlist)->createPlaylistFromPH($this->playlist_url);
    }

    // public function dddodownloadPlaylist()
    // {
    //     $this->youtube->playlist = $this->playlist;
    //     $fileArray               = file($this->playlist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    //     $count                   = count($fileArray);

    //     foreach ($fileArray as $i => $download_url) {
    //         $url                         = trim($download_url);
    //         $this->youtube->num_of_lines = $count;
    //         $count--;
    //         if (Option::istrue('max')) {
    //             if (Option::getValue('max') < $i - 1) {
    //                 continue;
    //             }
    //         }
    //         // utmdump(['i' => $i, 'max' => Option::getValue('max') - 1, 'cur' => $i - 1, 'url' => $url]);

    //         if ($url != '') {
    //             $this->youtube->run($url)->downloadPlaylist();

    //             // utmdd($url);
    //         }
    //     }
    //     $this->secondRun = true;

    //     $this->premiumIds = $this->youtube->premiumIds;
    //     $this->docompactPlaylist();
    // }

    public function dodownloadPlaylist()
    {
        // utminfo(func_get_args());
        // foreach (file($this->playlist) as $download_url) {
        //     // utmdump($download_url);
        // }
        // utmdd($download_url);

        if (Option::istrue('url')) {
            $this->youtubeWatchPlaylist();

            return true;
        }

        $this->youtube->run($this->playlist)->downloadPlaylist();
        $this->premiumIds = $this->youtube->premiumIds;
        $this->secondRun  = true;

        $this->docompactPlaylist();
    }

    public function premium()
    {
        $this->youtube->run($this->playlist)->downloadPlaylist(false);
        $this->premiumIds      = $this->youtube->premiumIds;
        $this->DownloadableIds = $this->youtube->DownloadableIds;

        $this->docompactPlaylist();
    }

    public function cleanBrkDownloads()
    {
        // utminfo(func_get_args());

        $files = Finder::Find('*.ytdl', __PLEX_DOWNLOAD__, exit: false);
        if ($files !== null) {
            foreach ($files as $file) {
                $info = pathinfo($file);
                $ytdl = $file;
                $mp4  = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
                $json = $info['dirname'] . DIRECTORY_SEPARATOR . basename($info['filename'], '.mp4') . '.info.json';

                Filesystem::delete($ytdl);
                Filesystem::delete($mp4);
                Filesystem::delete($json);
            }
        }
    }

    public static function Cleanup()
    {
        // utminfo(func_get_args());

        $archive_array = [];
        $playlistArray = [];
        if (self::$current_key !== false) {
            $current_key     = self::$current_key;
            $archive_content = Filesystem::readLines(self::$ARCHIVE);
            foreach ($archive_content as $lineNum => $line) {
                if (! str_contains($line, $current_key)) {
                    $archive_array[] = $line;
                }
            }
            // utmdump(__METHOD__);
            Filesystem::writeFile(self::$ARCHIVE, $archive_array);

            $files = Finder::Find('*' . $current_key . '*', __PLEX_DOWNLOAD__, exit: false);
            foreach ($files as $k => $file) {
                Filesystem::delete($file);
            }
        }

        if (self::$trimmedPlaylist !== false) {
            if (self::$originalPlaylist !== false) {
                $trimmedArray = Filesystem::readLines(self::$trimmedPlaylist);
                $orginalArray = Filesystem::readLines(self::$originalPlaylist);

                $playlistArray = array_merge($trimmedArray, $orginalArray);
                $playlistArray = array_unique($playlistArray, SORT_STRING);
                Filesystem::writeFile(self::$originalPlaylist, $playlistArray);
            }
        }
    }

    public function trimPlaylist()
    {
        // utminfo(func_get_args());

        $playlist_array = file($this->playlist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        (int) $max       = Option::getValue('max');
        $trimed_playlist = array_slice($playlist_array, 0, $max);

        $remaining_playlist = array_slice($playlist_array, $max);

        $this->OrigPlaylist = $this->playlist;
        $this->playlist     = Mediatag::$filesystem->tempnam(__PLEX_PL_TMP_DIR__, 'playlist_', '.txt');

        self::$trimmedPlaylist  = $this->playlist;
        self::$originalPlaylist = $this->OrigPlaylist;

        Filesystem::writePlaylist($this->playlist, $trimed_playlist);
        Filesystem::writePlaylist($this->OrigPlaylist, $remaining_playlist);
    }

    public function clean()
    {
        // utminfo(func_get_args());

        $array    = [];
        $playlist = [];

        Mediatag::$SearchArray = Mediatag::$finder->ExecuteSearch();
        $ids                   = $this->getDownloadedIds();

        foreach (Mediatag::$SearchArray as $key => $filename) {
            $video_key = MediaFile::getVideoKey($filename);

            if (in_array($video_key, $ids)) {
                $id_keys  = array_search($video_key, $ids);
                $newids[] = 'pornhub ' . $video_key;
                unset($ids[$id_keys]);
                // $array[] = $video_key;
                // utmdd("key " . $filename);
            } else {
                if (! str_starts_with($video_key, 'x')) {
                    if (file_exists($filename)) {
                        $newids[] = 'pornhub ' . $video_key;

                        continue;
                    }
                    $playlist[] = 'https://www.pornhub.com/view_video.php?viewkey=' . $video_key;
                }
            }
        }

        // utmdump(__METHOD__);
        Filesystem::writeFile(self::$ARCHIVE . '.new', $newids);
        if (count($playlist) > 0) {
            Filesystem::writeFile(self::PLAYLIST . '.new', $playlist);
        }
    }

    public function docompactPlaylist($firstRun = false)
    {
        if (Option::istrue('url')) {
            return '';
        }

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

            $f      = file($this->playlist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $before = count($f);

            $idCnt = count($this->ids);
            // utmdd([$before, $idCnt]);
            if ($before > 0) {
                $array = Filesystem::readLines($this->playlist, [$this, 'compactPlaylist']);
                $array = array_unique($array);
                $after = count($array);

                Mediatag::$output->writeln(PHP_EOL . 'before, <info>' . $before . '</info> and now after, <info>' . $after . ' </info>');
                $trimmedLines = $before - $after;
                Filesystem::writePlaylist($this->playlist, $array);
                $text = 'trimmed ' . $trimmedLines . ' from the playlist';
                Mediatag::$output->writeln('<info>' . $text . '</info>');
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

    public function filemap($line)
    {
        if ($line != '') {
            $ph_id = Strings::after($line, '=');
            if (str_contains($ph_id, '&')) {
                $ph_id = Strings::before($ph_id, '&');
            }
            $file_map[$ph_id] = [
                'url' => Strings::after($line, '-> '),
                'old' => Strings::before($line, ' ->'),
            ];

            return $file_map;
        }

        return false;
    }

    public function getpremiumListIds($line)
    {
        $ph_id = Strings::after($line, '=');
        if (str_contains($ph_id, '&')) {
            $ph_id = Strings::before($ph_id, '&');
        }

        return $ph_id;
    }

    public function compactPlaylist($line)
    {
        $ph_id = Strings::after($line, '=');
        if (str_contains($ph_id, '&')) {
            $ph_id = Strings::before($ph_id, '&');
        }
        if ($ph_id === null) {
            $ph_id = Strings::after($line, 'watch/');
            if ($ph_id !== null) {
                $ph_id = Strings::before($ph_id, '/');
            }
        }

        if (! in_array($ph_id, $this->ids)) {
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

    public function studioList($line)
    {
        // utminfo(func_get_args());

        if ($line != '') {
            $studioReplacement = '';
            $studio            = $line;
            if (str_contains($line, ':')) {
                $studio            = Strings::before($line, ':');
                $studioReplacement = ':' . Strings::after($line, ':');
            }

            return $studio . $studioReplacement;
        }

        return false;
    }

    public function studioPaths($line)
    {
        // utminfo(func_get_args());

        if ($line != '') {
            if (! str_contains($line, ':')) {
                $line = $line . ':' . $line;
            }

            $studio_match = Strings::before($line, ':');
            $studio_match = strtolower(str_replace(' ', '_', $studio_match));

            return [
                $studio_match => Strings::after($line, ':'),
            ];
        }

        return false;
    }

    public function toList($line)
    {
        // utminfo(func_get_args());

        if ($line != '') {
            $Replacement = $line;
            $match       = $line;
            if (str_contains($line, ':')) {
                $match       = Strings::before($line, ':');
                $Replacement = Strings::after($line, ':');
                if ($Replacement == '') {
                    $Replacement = null;
                }
            }
            $key = strtolower($match);
            $key = str_replace(' ', '_', $key);
            $key = str_replace('+', '', $key);
            $key = str_replace('(', '', $key);
            $key = str_replace(')', '', $key);

            return [$key => $Replacement];
        }

        return false;
    }

    public function toArray($line)
    {
        // utminfo(func_get_args());

        if ($line != '') {
            $Replacement = null;
            $match       = $line;
            if (str_contains($line, ':')) {
                $match       = Strings::before($line, ':');
                $Replacement = Strings::after($line, ':');
            }
            $key = strtolower($match);
            $key = str_replace(' ', '_', $key);
            $key = str_replace('+', '', $key);
            $key = str_replace('(', '', $key);
            $key = str_replace(')', '', $key);

            return [$key => $Replacement];
        }

        return false;
    }
}
