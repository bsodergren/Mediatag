<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Callbacks\traits;

use const PHP_EOL;

use Mediatag\Core\Mediatag;
use function array_key_exists;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Executable\MediatagExec;

use Mediatag\Modules\Executable\Helper\VideoDownloader;
use Mediatag\Commands\Playlist\Process as PlaylistProcess;

trait YtdlpCallBacks
{
    use CallbackCommon;
    use DownloadStrings;

    public function watchlistCallback($type, $buffer)
    {
        $buffer = MediatagExec::cleanBuffer($buffer);
        VideoDownloader::LogBuffer('' . $type . '', $buffer, 'playlist.log');

        return $buffer . PHP_EOL;
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
        $buffer = MediatagExec::cleanBuffer($buffer);

        // $outputText = '';
        // $line_id    = \PHP_EOL . '<id>' . $this->num_of_lines . '</id>';
        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/json" . ".log", $buffer . PHP_EOL);
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
}
