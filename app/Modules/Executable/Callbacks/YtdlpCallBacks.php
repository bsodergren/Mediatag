<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks;

use const TEST_EOL;
use Mediatag\Core\Mediatag;

use function array_key_exists;
use Mediatag\Modules\Filesystem\MediaFile;

use Mediatag\Modules\Executable\Helper\DownloadStrings;
use Mediatag\Commands\Playlist\Process as PlaylistProcess;

trait YtdlpCallBacks
{
    use CallbackCommon;
    use DownloadStrings;

    public function Pornhub($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if (str_contains($buffer, 'webpage')) {
            $this->num_of_lines--;
            $line_id = '<id>' . $this->num_of_lines . '</id>';

            $outputText = $line_id . ' <text>Trying to download  ' . $this->key . '  </text>';
        }

        return $outputText;
    }

    public function NubilesPorn($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if ($this->key !== null) {
            // // utmdump($buffer,$this->key);

            if (str_contains($buffer, $this->key . ': Downloading')) {
                $this->num_of_lines--;
                $line_id = '<id>' . $this->num_of_lines . '</id>';

                $outputText = $line_id . ' <text>Trying to download  ' . $this->key . '  </text>';
            }
        }

        // // utmdump([__LINE__,$outputText]);
        return $outputText;
    }

    public function watchlistCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);
        MediaFile::file_append_file(__LOGFILE_DIR__ . '/buffer/playlist.log', $buffer . TEST_EOL);

        return $buffer . TEST_EOL;
        // if (str_contains($buffer, '[PLAYLIST]')) {
        //     $this->Console->writeln($buffer);
        // // if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):?\s?+(.*)?/', $buffer, $matches)) {
        // //     if (\array_key_exists(2, $matches)) {
        // //         if ('' != $matches[2]) {
        // //             $outputText                   = '  <id> '.$matches[2].' cancelled </id>';
        // //             $this->Console->writeln($outputText);

        // //         }
        // //     }
        // // }
        // } else {
        //     $this->key = $buffer;
        //     $this->updatePlaylist($this->pltype);
        // }
        // $this->Console->writeln($this->key );
    }

    public function downloadJsonCallback($type, $buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        // $outputText = '';
        // $line_id    = \TEST_EOL . '<id>' . $this->num_of_lines . '</id>';
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/json" . ".log", $buffer . TEST_EOL);
        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (array_key_exists(2, $matches)) {
                if ($matches[2] != '') {
                    $this->key = $matches[2];
                }
            }
        }

        switch ($buffer) {
            // case str_contains($buffer, 'ERROR:'):
            //     $this->yt_json_string = null;
            //     // return $this->error($buffer,$this->num_of_lines,$matches[3]);
            //     return null;
            //     break;

            case str_contains($buffer, '[info]'):
                if (str_contains($buffer, 'as JSON')) {
                    $this->yt_json_string = $buffer;
                }
                break;
        }
    }

    public function error($buffer, $line_id, $error)
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText                   = '';
        PlaylistProcess::$current_key = false;
        $outputText                   = $line_id . '  <error> ' . $this->key . ' ' . $error . ' </error>';

        // $this->Console->writeln($outputText);
        // $this->updateIdList(PlaylistProcess::DISABLED);
        return $outputText . TEST_EOL;
    }

    public function downloadableIds($buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        $outputText = '';

        if (str_contains($buffer, 'Downloading')) {
            if (str_contains($buffer, $this->key)) {
                $outputText = "\t" . ' <file>file ' . $this->key . ' is downloadable </file>';
                $this->updatePlaylist('watchlater', 'trimmed_list.txt');
                $this->DownloadableIds[] = $this->key;
            }
        }

        return $outputText . TEST_EOL;
    }

    public function downloadVideo($buffer, $line_id)
    {
        // $buffer = $this->cleanBuffer($buffer);

        PlaylistProcess::$current_key = $this->key;
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->key . ".log", $buffer . TEST_EOL);
        $bufferMethod = 'Progress';
        $newLine      = false;
        // if (str_contains($buffer, 'Destination')) {

        // }

        if (str_contains($buffer, 'Destination')) {
            $bufferMethod = 'Destination';
            $newLine      = false;
            // $buffer = $this->cleanBuffer($buffer);


            //            return $this->ytlpDownloadBuffer('Destination',$buffer);
            // utmdump($buffer);
            // $outputText = str_replace("\n" . '[download]', '</text>' . TEST_EOL . PHP_TAB . '<text>[download]', $buffer);
            // $outputText = '<text>' . str_replace(__PLEX_DOWNLOAD__, '', $outputText) . '</file>' . TEST_EOL;

            // $outputText = PHP_TAB . str_replace('Destination:', 'Destination:</text> <file>', $outputText);

            // // utmdump([__LINE__, $outputText]);
            // return $outputText;
        }

        if (str_contains($buffer, 'already been')) {
            $bufferMethod = 'Exists';
            $newLine      = true;
            $this->num_of_lines--;
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
            Mediatag::error("There was an error " . $key);
        }

        return $this->ytlpDownloadBuffer($key, $buffer, $line_id, false);


    }
}
