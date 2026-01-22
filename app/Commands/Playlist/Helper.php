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
            $this->url = Option::getValue('url');
        }
        $youtube = new Youtube($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->createWatchList($this->url);
    }

    public function dodownloadPlaylistURL()
    {
        $youtube = new Youtube($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->downloadPlaylist();
        $this->secondRun = true;
    }

    public function dodownloadPlaylist()
    {
        // utminfo(func_get_args());

        $youtube = new Youtube($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->downloadPlaylist();
        $this->premiumIds = $youtube->premiumIds;
        $this->secondRun  = true;

        $this->docompactPlaylist();
    }

    public function premium()
    {
        $youtube = new Youtube($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->downloadPlaylist(false);
        $this->premiumIds      = $youtube->premiumIds;
        $this->DownloadableIds = $youtube->DownloadableIds;

        $this->docompactPlaylist();
    }

    public function missing()
    {
        // utminfo(func_get_args());

        foreach (Mediatag::$SearchArray as $filename) {
            $success = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
            if ($success == 1) {
                $video_keys[$matches[1]] = true;
            }
        }
        $playlist_file = 'missing_playlist.txt';
        $ids           = $this->getDownloadedIds();
        foreach ($ids as $id) {
            if (! array_key_exists($id, $video_keys)) {
                $missing[] = $id;
            }
        }
        $file_string = '';
        foreach ($missing as $v => $key) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
        }

        Filesystem::writeFile($playlist_file, $file_string);
    }

    public function find()
    {
        // utminfo(func_get_args());

        $files = Finder::Find('*.mp4', __PLEX_HOME__ . '/Pornhub', exit: false);

        foreach ($files as $file) {
            $key = File::getVideoKey(basename($file));
            if (str_starts_with($key, 'x')) {
                continue;
            }

            $existing_ids[] = $key;
            $archive_ids[]  = 'pornhub ' . $key;
        }

        Mediatag::$output->writeln('<info> found ' . count($archive_ids) . ' files</info>');
        $this->ids = $existing_ids;

        $archive_content = Filesystem::readLines(self::$ARCHIVE);
        $diff            = array_diff($archive_content, $archive_ids);
        if (is_array($diff)) {
            Mediatag::$output->writeln('<info> found ' . count($diff) . ' missing files</info>');
            if (count($diff) > 0) {
                foreach ($diff as $lineNum => $line) {
                    $idList[] = Strings::after($line, ' ');
                }

                $file_string = '';
                foreach ($idList as $v => $key) {
                    $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
                }

                Filesystem::writeFile(self::MISSING_PLAYLIST, $file_string);
            }
        }

        // utmdd($file_string);
        // $archive_content = array_merge($archive_content, $archive_ids);
        // $archive_array   = array_unique($archive_content);
        $archive_array = array_unique($archive_ids);

        Filesystem::writeFile(self::$ARCHIVE, $archive_array);

        return 0;
        if (isset($this->playlist)) {
            $f      = file($this->playlist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $before = count($f);
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

    public function cleanjSon()
    {
        // utminfo(func_get_args());

        $file_string = '';
        $file_array  = Mediatag::$finder->Search(__JSON_CACHE_DIR__, '*.json', exit: false);
        foreach ($file_array as $key => $val) {
            $ph_key = basename($val, '.info.json');
            if (! str_starts_with($ph_key, 'x')) {
                $this->json_Array[$ph_key] = $val;
            }
        }

        //   $file_array  = Mediatag::$finder->ExecuteSearch();

        foreach (Mediatag::$SearchArray as $key => $val) {
            if (array_key_exists($key, $this->json_Array)) {
                unset($this->json_Array[$key]);
            }
        }

        //        $archive_array = Filesystem::readLines(self::$ARCHIVE, [$this, 'parseArchive']);
        //        Filesystem::writeFile(self::$ARCHIVE, $archive_array);

        foreach ($this->json_Array as $key => $v) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;
        }

        Filesystem::writeFile(self::JSONPLAYLIST, $file_string);
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

        Filesystem::writeFile(self::$ARCHIVE . '.new', $newids);
        if (count($playlist) > 0) {
            Filesystem::writeFile(self::PLAYLIST . '.new', $playlist);
        }
    }

    public function docompactPlaylist($firstRun = false)
    {
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
            // utmdd([$before,$idCnt]);
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

    public function splitPlaylist()
    {
        (int) $split = Option::getValue('split');
        $splitName   = basename($this->playlist, '.txt');
        MediaFile::splitFile($this->playlist, './batch/', $split, $splitName . '_', '.txt');

        exit;
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

        // utmdd([$line,$ph_id]);
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
