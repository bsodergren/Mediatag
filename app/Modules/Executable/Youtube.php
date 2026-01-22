<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use const FILE_APPEND;
use const PHP_EOL;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Modules\Executable\Callbacks\traits\YtdlpCallBacks;
use Mediatag\Modules\Executable\Helper\Pornhub;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Nette\Utils\Callback;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;

class Youtube extends MediatagExec
{
    use YtdlpCallBacks;

    public $execMode = 'write';

    public $DownloadableIds = [];

    public $premium;

    public $num_of_lines;

    public $disabled = [];

    public $premiumIds = [];

    public $playlist;

    public $key;

    public $model_hub;

    public $downloadFiles = true;

    public $commonOptions = [
        CONFIG['YOUTUBEDL_CMD'],
        '-i',
        '-f',
        'bestvideo[width<=?1080]+bestaudio/best',
        // 'worstvideo[width<=?1080]+worstaudio/worst',
        '--restrict-filenames',
        // '-w',
        '-c',
        // '--no-part',
        '--write-info-json',
        '--no-warnings',
        '--ignore-config',
        '--write-info-json',
        '--write-subs',
        '--sub-format',
        'srt',
        '--sub-langs',
        'en',
    ];

    // private $jsonoptions = [
    //     '-f',
    //     'bestvideo[width<=?1080]+bestaudio/best',
    //     '-o',
    //     __JSON_CACHE_DIR__.'/updates/%(uploader)s/%(title)s-%(id)s.%(ext)s',
    //     '--restrict-filenames',
    //     '-w',
    //     '-c',
    //     '--no-part',
    //     // '--write-info-json',
    // ];
    public $Console;

    public $yt_json_string;

    public $pltype;

    private $LibraryClass;

    public const __YT_DL_FORMAT__ = '%(uploader)s/%(title)s-%(id)s.%(ext)s';

    public $buffer_file = __APP_HOME__ . '/var/log/buffer.txt';

    public $library;

    public function __construct($class, $input = null, $output = null)
    {
        // utminfo(func_get_args());

        $this->Console = new ConsoleOutput(Mediatag::$output, Mediatag::$input);

        // utmdd($this->library);

        if (is_file($class)) {
            $this->playlist = $class;
            $st_array       = file($this->playlist);
            $class          = $st_array[0];
        } else {
            $class = 'Pornhub';
        }

        if (str_contains($class, 'pornhub')) {
            $class = 'Pornhub';
        }
        if (str_contains($class, 'eporner')) {
            $class = 'Eporner';
        }
        if (str_contains($class, 'nubiles')) {
            $class = 'Studio';
        }
        $this->library = $class;
        //        use Mediatag\Modules\Executable\Helper\Studio;

        $Class = 'Mediatag\\Modules\\Executable\\Helper\\' . $class;
        // utmdd($class);
        $this->LibraryClass = new $Class($this);

        // $this->commonOptions = [
        //     CONFIG['YOUTUBEDL_CMD'],
        //     '-i',
        //     '-f',
        //     'bestvideo[width<=?1080]+bestaudio/best',
        //     // 'worstvideo[width<=?1080]+worstaudio/worst',
        //     '--restrict-filenames',
        //     // '-w',
        //     '-c',
        //     // '--no-part',
        //     '--write-info-json',
        //     '--no-warnings',
        //     '--ignore-config',
        // ];
    }

    public function youtubeGetJson($video_key)
    {
        // utminfo(func_get_args());
        if ($this->library != 'Pornhub') {
            return null;
        }
        // https://www.pornhub.com/view_video.php?viewkey=ph63403d856ceac
        $options   = array_merge($this->commonOptions, $this->LibraryClass->options);
        $options   = array_merge($options, ['--skip-download']);
        $video_url = 'https://www.pornhub.com/view_video.php?viewkey=' . $video_key;

        $command = array_merge($options, [$video_url]);

        $callback = Callback::check([$this, 'downloadJsonCallback']);
        $this->exec($command, $callback);
        preg_match('/(\/[a-zA-Z0-9-\/_@.]+)/', $this->yt_json_string, $output_array);
        $json_file = '';

        if (array_key_exists(1, $output_array)) {
            $json_file = $output_array[1];
            $this->moveJson($json_file);
        }
        // UtmDd($json_file,$this->yt_json_string);

        return $json_file;
    }

    public function youtubeCmdOptions()
    {
        $options = array_merge($this->commonOptions, $this->LibraryClass->options);
        if (
            ! Option::istrue('ignore')
            && ! Option::istrue('skip')
            && $this->downloadFiles === true
            && ! Option::istrue('archive')
        ) {
            $options = array_merge($options, [
                '--download-archive',
                PlaylistProcess::$ARCHIVE,
            ]);
        }

        if (Option::istrue('archive')) {
            // utmdump(['archive',                PlaylistProcess::$ARCHIVE]);
            $options = array_merge($options, [
                '--download-archive',

                __PLEX_PL_DIR__ . '/ids/' . Option::getValue('archive') . '.txt',
                '--force-write-archive',
            ]);
        }

        if (Option::istrue('skip') || $this->downloadFiles === false) {
            $options = array_merge($options, ['--skip-download']);
        }

        if (Option::istrue('max')) {
            $options = array_merge($options, ['--max-downloads', Option::getValue('max')]);
        }

        // utmdd($options, Option::getOptions());
        $playlist_opt = ['-a', $this->playlist];

        if (Option::istrue('url')) {
            $playlist_opt = [Option::getValue('url')];
        }

        return array_merge($options, $playlist_opt);
    }

    public function createWatchList($url)
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('<info> Downloading video URLs from playlist </info>');
        // $this->pltype = 'watchlater';
        // if (str_contains($url, 'premium')) {
        //     $this->pltype = 'watchlaterPr';
        // }

        $command = array_merge($this->commonOptions, [$url]);
        $this->exec($command, Callback::check([$this, 'watchlistCallback']));
    }

    public function downloadPlaylist($downloadFiles = true)
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('<info> Downloaded new Playlist </info>');

        $this->downloadFiles = $downloadFiles;
        $this->num_of_lines  = 100;

        if (! Option::istrue('url')) {
            $names = file($this->playlist);
            // utmdd($names);
            if (Option::istrue('max')) {
                $this->num_of_lines = (int) Option::getValue('max', true);
            } else {
                $this->num_of_lines = count($names) + 1;
            }

            if (! str_contains('premium', $this->playlist)) {
                $this->premium = str_replace('.txt', '_premium.txt', $this->playlist);
                Filesystem::backupPlaylist($this->premium);
            }

            if (! str_contains('model_hub', $this->playlist)) {
                $this->model_hub = str_replace('.txt', '_model_hub.txt', $this->playlist);
                Filesystem::backupPlaylist($this->model_hub);
            }

            // } else {
            //     $callback = Callback::check([$this->LibraryClass, 'watchlistCallback']);
        }

        $this->LibraryClass->init($this);
        $callback = Callback::check([$this->LibraryClass, 'downloadCallback']);
        $command  = $this->youtubeCmdOptions();
        $this->exec($command, $callback);
    }

    public function moveJson($json_file)
    {
        // utminfo(func_get_args());

        // $old_name = $videoInfo['video_name'];
        // $old_path = $videoInfo['video_path'];
        $json_key = '';
        // $json_file = $old_path.'/'.basename($old_name, 'mp4').'info.json';
        if (Mediatag::$filesystem->exists($json_file)) {
            $success = preg_match('/-(p?h?[a-z0-9]+).info.json/', basename($json_file), $matches);
            if ($success === 1) {
                $json_key = $matches[1];
            } else {
                // utmdd($matches);
            }
        }

        $newJson_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';

        if (Mediatag::$filesystem->exists($json_file)) {
            if (! Mediatag::$filesystem->exists($newJson_file)) {
                if (Option::istrue('test')) {
                    $out = "<question>jSon</question>\n\t<comment>Old:" . basename($json_file) . "</comment>\n\t<info>New:" . basename($newJson_file) . '</info>';
                    Mediatag::$output->writeln($out);
                } else {
                    Mediatag::$filesystem->rename($json_file, $newJson_file, false);
                }
            }

            return true;
        }

        return false;
    }
}
