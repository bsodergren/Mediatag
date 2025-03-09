<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Modules\Executable\Callbacks\YtdlpCallBacks;
use Mediatag\Modules\Executable\Helper\Pornhub;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Nette\Utils\Callback;
use UTM\Utilities\Option;

class Youtube extends MediatagExec
{
    use YtdlpCallBacks;

    public $execMode = 'write';

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

    public $buffer_file = __APP_HOME__.'/var/log/buffer.txt';

    public $library;

    public function __construct($class, $input = null, $output = null)
    {
        // utminfo(func_get_args());

        $this->Console = new ConsoleOutput(Mediatag::$output, Mediatag::$input);

        // utmdd($this->library);
        if (is_file($class)) {
            $this->playlist = $class;
            $st_array       = file($this->playlist);

            $class = $st_array[0];
        }

        if (str_contains($class, 'pornhub')) {
            $class = 'Pornhub';
        }
        if (str_contains($class, 'nubiles')) {
            $class = 'Studio';
        }

        // utmdd($class);

        $this->library = $class;
        //        use Mediatag\Modules\Executable\Helper\Studio;

        $Class              = 'Mediatag\\Modules\\Executable\\Helper\\'.$class;
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
        if ('Pornhub' != $this->library) {
            return null;
        }
        // https://www.pornhub.com/view_video.php?viewkey=ph63403d856ceac
        $options   = array_merge($this->commonOptions, $this->LibraryClass->options);
        $options   = array_merge($options, ['--skip-download', '--write-info-json']);
        $video_url = 'https://www.pornhub.com/view_video.php?viewkey='.$video_key;

        $command = array_merge($options, [$video_url]);

        $callback = Callback::check([$this, 'downloadJsonCallback']);
        $this->exec($command, $callback);

        preg_match('/(\/[a-zA-Z0-9-\/_@.]+)/', $this->yt_json_string, $output_array);
        $json_file = '';

        if (\array_key_exists(1, $output_array)) {
            $json_file = $output_array[1];
            $this->moveJson($json_file);
        }

        return $json_file;
    }

    public function youtubeCmdOptions()
    {
        $options = array_merge($this->commonOptions, $this->LibraryClass->options);
        if (!Option::istrue('ignore') && !Option::istrue('skip') && true === $this->downloadFiles) {
            $options = array_merge($options, [
                '--download-archive',
                __PLEX_PL_DIR__.'/ids/archive.txt',
                '--write-info-json',
                '--embed-thumbnail',
            ]);
        }
        if (Option::istrue('skip') || false === $this->downloadFiles) {
            $options = array_merge($options, ['--skip-download']);
        }

        $playlist_opt = ['-a', $this->playlist];

        return array_merge($options, $playlist_opt);
    }

    public function createWatchList($url)
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('<info> Downloading video URLs from playlist </info>');
        $this->pltype = 'watchlater';
        if (str_contains($url, 'premium')) {
            $this->pltype = 'watchlaterPr';
        }

        $command = array_merge($this->commonOptions, ['--get-id', $url]);
        $this->exec($command, Callback::check([$this, 'watchlistCallback']));
    }

    public function downloadPlaylist($downloadFiles = true)
    {
        // utminfo(func_get_args());

        Mediatag::$output->writeln('<info> Downloaded new Playlist </info>');

        $this->downloadFiles = $downloadFiles;

        $names              = file($this->playlist);
        $this->num_of_lines = \count($names) + 1;

        $command = $this->youtubeCmdOptions();
        if (!str_contains('premium', $this->playlist)) {
            $this->premium = str_replace('.txt', '_premium.txt', $this->playlist);
            Filesystem::backupPlaylist($this->premium);
        }

        if (!str_contains('model_hub', $this->playlist)) {
            $this->model_hub = str_replace('.txt', '_model_hub.txt', $this->playlist);
            Filesystem::backupPlaylist($this->model_hub);
        }

        $callback = Callback::check([$this->LibraryClass, 'downloadCallback']);
        // utmdd($command);
        $this->exec($command, $callback);
    }

    private function updateIdList($keyfile)
    {
        // utminfo(func_get_args());

        file_put_contents($keyfile, $this->key.\PHP_EOL, \FILE_APPEND);
    }

    private function updatePlaylist($type, $file = null)
    {
        // utminfo(func_get_args());
        if (null === $file) {
            $file = $this->playlist;
        }

        if ('watchlaterPr' == $type) {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey='.$this->key;
            // $this->Console->writeln($url);
            file_put_contents($file, $url.\PHP_EOL, \FILE_APPEND);

            return 1;
        }
        if ('watchlater' == $type) {
            $url = 'https://www.pornhub.com/view_video.php?viewkey='.$this->key;
            // $this->Console->writeln($url);
            file_put_contents($file, $url.\PHP_EOL, \FILE_APPEND);

            return 1;
        }

        if ('premium' == $type) {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey='.$this->key;
            // $this->Console->writeln($url);
            if (!str_contains('premium', $file)) {
                file_put_contents($this->premium, $url.\PHP_EOL, \FILE_APPEND);
            }

            return 1;
        }

        if ('modelhub' == $type) {
            $url = 'https://www.modelhub.com/video/'.$this->key;
            if (!str_contains('model_hub', $file)) {
                file_put_contents($this->model_hub, $url.\PHP_EOL, \FILE_APPEND);
            }

            return 1;
        }
        if ('error' == $type) {
            $url = 'https://www.pornhub.com/view_video.php?viewkey='.$this->key;
            file_put_contents(PlaylistProcess::ERRORPLAYLIST, $url.\PHP_EOL, \FILE_APPEND);

            return 1;
        }
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
            if (1 === $success) {
                $json_key = $matches[1];
            } else {
                // utmdd($matches);
            }
        }

        $newJson_file = __JSON_CACHE_DIR__.'/'.$json_key.'.info.json';

        if (Mediatag::$filesystem->exists($json_file)) {
            if (!Mediatag::$filesystem->exists($newJson_file)) {
                if (Option::istrue('test')) {
                    $out = "<question>jSon</question>\n\t<comment>Old:".basename($json_file)."</comment>\n\t<info>New:".basename($newJson_file).'</info>';
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
