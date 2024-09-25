<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\YoutubeExec;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
// use Nette\Utils\FileSystem as NetteFile;
use Mediatag\Modules\Filesystem\MediaFinder as Finder;
use Nette\Utils\Strings;
use UTM\Utilities\Option;

trait Helper
{
    public $url    = 'https://www.pornhub.com/playlist/watchlater';
    public $idList = [];

    public function youtubeWatchPlaylist()
    {
        utminfo();

        Mediatag::$output->writeln('<info> downloading the watchlater list </info>');

        if (Option::istrue('url')) {
            $this->url = Option::getValue('url');
        }
        $youtube = new YoutubeExec($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->createWatchList($this->url);
    }

    public function download()
    {
        utminfo();

        $youtube          = new YoutubeExec($this->playlist, Mediatag::$input, Mediatag::$output);
        $youtube->downloadPlaylist();
        $this->premiumIds = $youtube->premiumIds;
        $this->compact();
    }

    public function missing()
    {
        utminfo();

        foreach (Mediatag::$SearchArray as $filename) {
            $success = preg_match('/-(p?h?[a-z0-9]{6,}).mp4/i', $filename, $matches);
            if (1 == $success) {
                $video_keys[$matches[1]] = true;
            }
        }
        $playlist_file = 'missing_playlist.txt';
        $ids           = $this->getDownloadedIds();
        foreach ($ids as $id) {
            if (! \array_key_exists($id, $video_keys)) {
                $missing[] = $id;
            }
        }
        $file_string   = '';
        foreach ($missing as $v => $key) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . \PHP_EOL;
        }

        Filesystem::writeFile($playlist_file, $file_string);
    }

    public function find()
    {
        utminfo();

        $files           = Finder::Find('*.mp4', __PLEX_HOME__ . '/Pornhub');


        foreach ($files as $file) {
            $key            = File::getVideoKey(basename($file));
            if (str_starts_with($key, 'x')) {
                continue;
            }

            $existing_ids[] = $key;
            $archive_ids[]  = 'pornhub ' . $key;
        }

        Mediatag::$output->writeln('<info> found ' . count($archive_ids) . ' files</info>');
        $this->ids       = $existing_ids;

        $archive_content = Filesystem::readLines(self::ARCHIVE);
        $archive_content = array_merge($archive_content, $archive_ids);
        $archive_array   = array_unique($archive_content);
        Filesystem::writeFile(self::ARCHIVE, $archive_array);

        if (isset($this->playlist)) {
            $f      = file($this->playlist, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
            $before = \count($f);
            if ($before > 0) {
                $array        = Filesystem::readLines($this->playlist, [$this, 'compactPlaylist']);
                $array        = array_unique($array);
                $after        = \count($array);

                Mediatag::$output->writeln('before, <info>' . $before . '</info> and now after, <info>' . $after . ' </info>');
                $trimmedLines = $before - $after;
                Filesystem::writePlaylist($this->playlist, $array);
                $text         = 'trimmed ' . $trimmedLines . ' from the playlist';
                Mediatag::$output->writeln('<info>' . $text . '</info>');
                if (0 == $after) {
                    Mediatag::$output->writeln('<info> All files downloaded</info>');
                    Filesystem::delete($this->playlist);

                    exit;
                }
            }
        }
    }

    public function cleanBrkDownloads()
    {
        utminfo();

        $files = Finder::Find('*.ytdl', __PLEX_DOWNLOAD__);
        if (null !== $files) {
            foreach ($files as $file) {
                $info = pathinfo($file);
                $ytdl = $file;
                $mp4  = $info['dirname'] . \DIRECTORY_SEPARATOR . $info['filename'];
                $json = $info['dirname'] . \DIRECTORY_SEPARATOR . basename($info['filename'], '.mp4') . '.info.json';
                Filesystem::delete($ytdl);
                Filesystem::delete($mp4);
                Filesystem::delete($json);
            }
        }
    }

    public static function Cleanup()
    {
        utminfo();

        $archive_array = [];
        $playlistArray = [];
        if (false !== self::$current_key) {
            $current_key     = self::$current_key;
            $archive_content = Filesystem::readLines(self::ARCHIVE);
            foreach ($archive_content as $lineNum => $line) {
                if (! str_contains($line, $current_key)) {
                    $archive_array[] = $line;
                }
            }
            Filesystem::writeFile(self::ARCHIVE, $archive_array);

            $files           = Finder::Find('*' . $current_key . '*', __PLEX_DOWNLOAD__);
            foreach ($files as $k => $file) {
                Filesystem::delete($file);
            }
        }

        if (false !== self::$trimmedPlaylist) {
            if (false !== self::$originalPlaylist) {
                $trimmedArray  = Filesystem::readLines(self::$trimmedPlaylist);
                $orginalArray  = Filesystem::readLines(self::$originalPlaylist);

                $playlistArray = array_merge($trimmedArray, $orginalArray);
                $playlistArray = array_unique($playlistArray, \SORT_STRING);
                Filesystem::writeFile(self::$originalPlaylist, $playlistArray);
            }
        }
    }

    public function trimPlaylist()
    {
        utminfo();

        $playlist_array         = file($this->playlist, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);

        (int) $max              = Option::getValue('max');
        $trimed_playlist        = \array_slice($playlist_array, 0, $max);

        $remaining_playlist     = \array_slice($playlist_array, $max);

        $this->OrigPlaylist     = $this->playlist;
        $this->playlist         = Mediatag::$filesystem->tempnam(__PLEX_PL_TMP_DIR__, 'playlist_', '.txt');

        self::$trimmedPlaylist  = $this->playlist;
        self::$originalPlaylist = $this->OrigPlaylist;

        Filesystem::writePlaylist($this->playlist, $trimed_playlist);
        Filesystem::writePlaylist($this->OrigPlaylist, $remaining_playlist);
    }

    public function cleanjSon()
    {
        utminfo();

        $file_string = '';
        $file_array  = Mediatag::$finder->Search(__JSON_CACHE_DIR__, '*.json');
        foreach ($file_array as $key => $val) {
            $ph_key                    = basename($val, '.info.json');
            $this->json_Array[$ph_key] = $val;
        }

        //   $file_array  = Mediatag::$finder->ExecuteSearch();

        foreach (Mediatag::$SearchArray as $key => $val) {
            if (\array_key_exists($key, $this->json_Array)) {
                unset($this->json_Array[$key]);
            }
        }

        //        $archive_array = Filesystem::readLines(self::ARCHIVE, [$this, 'parseArchive']);
        //        Filesystem::writeFile(self::ARCHIVE, $archive_array);

        foreach ($this->json_Array as $key => $v) {
            $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . \PHP_EOL;
        }

        Filesystem::writeFile(self::JSONPLAYLIST, $file_string);
    }

    public function clean()
    {
        utminfo();

        $array                 = [];
        $playlist              = [];

        Mediatag::$SearchArray = Mediatag::$finder->ExecuteSearch();
        $ids                   = $this->getDownloadedIds();

        foreach (Mediatag::$SearchArray as $key => $filename) {
            $video_key = MediaFile::getVideoKey($filename);

            if (in_array($video_key, $ids)) {
                $id_keys  = array_search($video_key, $ids);
                $newids[] = "pornhub " . $video_key;
                unset($ids[$id_keys]);
                // $array[] = $video_key;
                //utmdd("key " . $filename);
            } else {

                if (! str_starts_with($video_key, "x")) {
                    if (file_exists($filename)) {
                        $newids[] = "pornhub " . $video_key;

                        continue;
                    }
                    $playlist[] = "https://www.pornhub.com/view_video.php?viewkey=" . $video_key;
                }
            }
        }

        Filesystem::writeFile(self::ARCHIVE . ".new", $newids);
        if (count($playlist) > 0) {
            Filesystem::writeFile(self::PLAYLIST . ".new", $playlist);
        }
    }

    public function compact()
    {
        utminfo();

        if (! Option::istrue('skip')) {
            $this->ids = $this->getDownloadedIds();

            if (!file_exists($this->playlist)) {
                Mediatag::$output->writeln('<info>File doesnt exist</info>');
                exit;
            }

            $f         = file($this->playlist, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
            $before    = \count($f);
            if ($before > 0) {
                $array        = Filesystem::readLines($this->playlist, [$this, 'compactPlaylist']);
                $array        = array_unique($array);
                $after        = \count($array);

                Mediatag::$output->writeln('before, <info>' . $before . '</info> and now after, <info>' . $after . ' </info>');
                $trimmedLines = $before - $after;
                Filesystem::writePlaylist($this->playlist, $array);
                $text         = 'trimmed ' . $trimmedLines . ' from the playlist';
                Mediatag::$output->writeln('<info>' . $text . '</info>');
                if (0 == $after) {
                    Mediatag::$output->writeln('<info> All files downloaded</info>');
                    Filesystem::delete($this->playlist);

                    exit;
                }
            }
        }
        if (Option::istrue('download')) {
            $this->download();
        }
    }

    public function getDownloadedIds()
    {
        utminfo();

        if (Option::isTrue('ignore')) {
            return [];
        }

        $archive_content = Filesystem::readLines(self::ARCHIVE);
        if (\is_array($archive_content)) {
            foreach ($archive_content as $lineNum => $line) {
                $this->idList[] = Strings::after($line, ' ');
            }
        }


        $fileidArray     = [
            0 => [self::DISABLED, 0],
            1 => [self::MODELHUB, 0],
            2 => [self::IGNORED, 1],
            3 => [self::ERRORIDS, 0],
        ];

        foreach ($fileidArray as $i => $fileId) {
            $file = $fileId[0];
            if (0 == $fileId[1]) {
                $idArray = Filesystem::readLines($file);

                if (\is_array($idArray)) {
                    $this->idList = array_merge($this->idList, $idArray);
                }
            }
        }
        $this->getpremiumIds();
        $this->idList    = array_merge($this->idList, $this->premiumIds);

        return $this->idList;
    }

    public function getpremiumIds()
    {
        utminfo();

        if (! str_contains('premium', $this->playlist)) {
            $this->premium = str_replace('.txt', '_premium.txt', $this->playlist);
            if (file_exists($this->premium)) {
                $this->premiumIds = Filesystem::readLines($this->premium, [$this, 'getpremiumListIds']);
            }
        }
    }
}
