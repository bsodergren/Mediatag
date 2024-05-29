<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Traits\Callables;
use Nette\Utils\Callback;
use UTM\Utilities\Option;

class YoutubeExec extends MediatagExec
{
    use Callables;

    public $execMode = 'write';

    public $premium;

    public $num_of_lines;

    public $disabled = [];

    public $premiumIds = [];

    public $playlist;

    public $key;

    public $model_hub;

    public $downloadFiles = true;

    public $commonOptions;

    public $Console;
    public $pltype;

    public $buffer_file = __APP_HOME__.'/var/log/buffer.txt';

    public function __construct($playlist, $input = null, $output = null)
    {
        $this->playlist = $playlist;

        $this->Console = new ConsoleOutput(Mediatag::$output, Mediatag::$input);
        $this->commonOptions = [
            CONFIG['YOUTUBEDL_CMD'],
            '-i',
            // '--username',
            // __PH_USERNAME__,
            // '--password',
            // __PH_PASSWORD__,
           '--no-warnings',
            '--ignore-config',
        ];
    }

    public function youtubeCmdOptions()
    {
        $options = [
            '-f',
            'bestvideo[width<=?1080]+bestaudio/best',
            '-o',
            __PLEX_DOWNLOAD__.'/%(uploader)s/%(title)s-%(id)s.%(ext)s',
            '--restrict-filenames',
            '-w',
            '-c',
            '--write-thumbnail',
            '--embed-thumbnail',
            '--no-part',
            '--write-info-json',
        ];

        $options = array_merge($this->commonOptions, $options);
        if (! Option::istrue('ignore') && ! Option::istrue('skip')) {
            $options = array_merge($options, ['--download-archive', __PLEX_PL_DIR__.'/ids/archive.txt']);
        }

        if (Option::istrue('skip')) {
            $options = array_merge($options, ['--skip-download']);
        }

        $playlist_opt = ['-a', $this->playlist];

        return array_merge($options, $playlist_opt);
    }

    public function createWatchList($url)
    {
        $this->Console->writeln('<info> Downloading video URLs from playlist </info>');
        $this->pltype = 'watchlater';
        if (str_contains($url, 'premium')) {
            $this->pltype = 'watchlaterPr';
        }

        $command = array_merge($this->commonOptions, ['--get-id', $url]);
        $this->exec($command, Callback::check([$this, 'watchlistCallback']));
    }

    public function downloadPlaylist($downloadFiles = true)
    {
        $this->Console->writeln('<info> Downloaded new Playlist </info>');

        $this->downloadFiles = $downloadFiles;

        $names = file($this->playlist);
        $this->num_of_lines = \count($names) + 1;

        $command = $this->youtubeCmdOptions();

        if (! str_contains('premium', $this->playlist)) {
            $this->premium = str_replace('.txt', '_premium.txt', $this->playlist);
            Filesystem::backupPlaylist($this->premium);
        }

        if (! str_contains('model_hub', $this->playlist)) {
            $this->model_hub = str_replace('.txt', '_model_hub.txt', $this->playlist);
            Filesystem::backupPlaylist($this->model_hub);
        }

        $callback = Callback::check([$this, 'downloadCallback']);
        $this->exec($command, $callback);
    }

    private function updateIdList($keyfile)
    {
        file_put_contents($keyfile, $this->key.\PHP_EOL, \FILE_APPEND);
    }

    private function updatePlaylist($type)
    {
        if ('watchlaterPr' == $type) {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey='.$this->key;
            $this->Console->writeln($url);
            file_put_contents($this->playlist, $url.\PHP_EOL, \FILE_APPEND);

            return 1;
        }
        if ('watchlater' == $type) {
            $url = 'https://www.pornhub.com/view_video.php?viewkey='.$this->key;
            $this->Console->writeln($url);
            file_put_contents($this->playlist, $url.\PHP_EOL, \FILE_APPEND);

            return 1;
        }

        if ('premium' == $type) {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey='.$this->key;
            $this->Console->writeln($url);

            if (! str_contains('premium', $this->playlist)) {
                file_put_contents($this->premium, $url.\PHP_EOL, \FILE_APPEND);
            }

            return 1;
        }

        if ('modelhub' == $type) {
            $url = 'https://www.modelhub.com/video/'.$this->key;
            if (! str_contains('model_hub', $this->playlist)) {
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
}
