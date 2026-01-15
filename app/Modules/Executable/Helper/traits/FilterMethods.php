<?php

namespace Mediatag\Modules\Executable\Helper\traits;

use const PHP_EOL;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Callbacks\traits\CallbackCommon;
use Mediatag\Modules\Executable\Callbacks\traits\DownloadStrings;
use Mediatag\Modules\Executable\Callbacks\traits\YtdlpCallBacks;
use Mediatag\Modules\Executable\Helper\VideoDownloader;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Utilities\Strings;

use function array_key_exists;

trait FilterMethods
{
    public $line_id = null;

    public $command = null;

    public function getShortName($filename)
    {
        $file      = str_replace(__PLEX_DOWNLOAD__, '', $filename);
        $file_name = basename($file);
        $file_path = str_replace($file_name, '', $file);
        $file_name = Strings::truncateString($file_name, 60, true, true);

        // $file_name = Strings::truncateString($file_name, 30, true);
        return $file_path . $file_name;
    }

    public function ytlpDownloadBuffer($command, $buffer, $line_id = null, $newLine = false): string
    {
        if ($command != 'Progress') {
            $buffer = MediatagExec::cleanBuffer($buffer);
        }

        $this->line_id = $line_id;
        $this->command = $command;
        $method        = 'download' . $command;

        $output = $this->$method($buffer);
        // $output        = str_replace("\n", "", $output);
        if ($newLine === true) {
            $output = PHP_EOL . $output;
        }

        return $output;
    }

    public function downloadDestination($buffer)
    {
        preg_match('/(\[[a-z]+\] [a-zA-Z0-9 :]+)(\[[a-z]+\] )?(Destination: )?(.*)/m', $buffer, $match);
        //preg_match('/(\[[a-z]+\] [a-zA-Z0-9 :]+)(\[[a-z]+\]) (Destination:) (.*)/m', $buffer, $match);
        if (array_key_exists(4, $match) === true) {
            $file = $this->getShortName($match[4]);
        }
        if (array_key_exists(3, $match) === true) {
            $text = $match[3];
        } else {
            $text = 'file downlaoding';
        }

        return PHP_TAB . '<text>' . $text . '<text> <file>' . $file . '</file>' . PHP_EOL;
    }

    public function downloadProgress($buffer)
    {
        $output = trim($buffer);
        $output = '<download>' . $buffer . '</>';

        if (str_contains($buffer, '100%')) {
            $output = $output . PHP_EOL;
        }

        return $output;
    }

    public function downloadExists($buffer)
    {
        VideoDownloader::LogBuffer('downloadExists = ' . $this->key . '', $buffer, 'download_error.log');
        // $this->num_of_lines--;

        return $this->line_id . '<error>' . $this->key . ' Already been downloaded </error>' . PHP_EOL;
    }

    public function downloadError($buffer)
    {
        VideoDownloader::LogBuffer('downloadError = ' . $this->key . '', $buffer, 'download_error.log');
        // $this->num_of_lines--;

        return '<error>' . $buffer . '</error>';
    }

    public function downloadFixupM3u8($buffer)
    {
        preg_match('/(\[[a-zA-Z0-9]+\])(.*)"(.*)"/m', $buffer, $match);
        $text = $match[1];
        $file = $this->getShortName($match[3]);
        $this->num_of_lines--;

        return PHP_TAB . '<text>' . $text . '<text> <file>' . $file . '</file>';
    }

    public function error($buffer, $line_id, $error)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);

        VideoDownloader::LogBuffer('PlaylistProcess::DISABLED Key = ' . $this->key . '', $buffer, 'download_error.log');

        $outputText                   = '';
        PlaylistProcess::$current_key = false;
        $outputText                   = $line_id . '  <error> ' . $this->key . ' ' . $error . ' </error>';

        // $this->Console->writeln($outputText);
        $this->updateIdList(PlaylistProcess::DISABLED);

        return $outputText;
    }

    public function updateIdList($keyfile)
    {
        // utminfo(func_get_args());
        $id = $this->KeyPrefix . ' ' . $this->key;
        file_put_contents($keyfile, $id . PHP_EOL, FILE_APPEND);
    }

    public function updatePlaylist($type, $file = null)
    {
        // utminfo(func_get_args());
        if ($file === null) {
            $file = $this->playlist;
        }

        if ($type == 'watchlaterPr') {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey=' . $this->key;
            // $this->Console->writeln($url);
            file_put_contents($file, $url . PHP_EOL, FILE_APPEND);

            return 1;
        }
        if ($type == 'watchlater') {
            $url = 'https://www.pornhub.com/view_video.php?viewkey=' . $this->key;
            // $this->Console->writeln($url);
            file_put_contents($file, $url . PHP_EOL, FILE_APPEND);

            return 1;
        }

        if ($type == 'premium') {
            $url = 'https://www.pornhubpremium.com/view_video.php?viewkey=' . $this->key;
            // $this->Console->writeln($url);
            if (! str_contains('premium', $file)) {
                file_put_contents($this->premium, $url . PHP_EOL, FILE_APPEND);
            }

            return 1;
        }

        if ($type == 'modelhub') {
            $url = 'https://www.modelhub.com/video/' . $this->key;
            if (! str_contains('model_hub', $file)) {
                file_put_contents($this->model_hub, $url . PHP_EOL, FILE_APPEND);
            }

            return 1;
        }
        if ($type == 'error') {
            $url = 'https://www.pornhub.com/view_video.php?viewkey=' . $this->key;
            $ret = file_put_contents(PlaylistProcess::ERRORPLAYLIST, $url . PHP_EOL, FILE_APPEND);

            return 1;
        }
    }

    public function downloadVideo($buffer, $line_id)
    {
        // $buffer = MediatagExec::cleanBuffer($buffer);

        PlaylistProcess::$current_key = $this->key;
        //        MediaFile::file_append_file(__LOGFILE_DIR__ . '/buffer/' . $this->key . '.log', $buffer . PHP_EOL);
        $bufferMethod = 'Progress';
        $newLine      = false;
        // if (str_contains($buffer, 'Destination')) {

        // }

        if (str_contains($buffer, 'Destination')) {
            $bufferMethod = 'Destination';
            $newLine      = false;
        }

        if (str_contains($buffer, 'already been')) {
            $bufferMethod = 'Exists';
            $newLine      = true;
            // $this->num_of_lines--;
            $this->updatePlaylist('errors', 'downloaded_list.txt');
        }
        if (str_contains($buffer, 'Got error')) {
            $bufferMethod = 'Error';
            $newLine      = true;
        }

        return $this->ytlpDownloadBuffer($bufferMethod, $buffer, $line_id, $newLine);
    }

    public function fixVideo($buffer, $line_id, $key = 'FixupM3u8')
    {
        if ($key != 'FixupM3u8') {
            Mediatag::error('There was an error ' . $key);
        }

        return $this->ytlpDownloadBuffer($key, $buffer, $line_id, false);
    }

    public function downloadableIds($buffer, $line_id)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);

        $outputText = '';

        if (str_contains($buffer, 'Downloading')) {
            if (str_contains($buffer, $this->key)) {
                $outputText = "\t" . ' <file>file ' . $this->key . ' is downloadable </file>';
                $this->updatePlaylist('watchlater', 'trimmed_list.txt');
                $this->DownloadableIds[] = $this->key;
            }
        }

        return $outputText . PHP_EOL;
    }
}
