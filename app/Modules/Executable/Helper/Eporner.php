<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

use function array_key_exists;

// define('PHP_EOL', '\n' . PHP_EOL);

class Eporner extends VideoDownloader
{
    public $options = [
        '-o',
        __PLEX_DOWNLOAD__ . '/Eporner/' . Youtube::__YT_DL_FORMAT__,
        //  '-u',
        //  CONFIG['PH_USERNAME'],
        //  '-p',
        //  CONFIG['PH_PASSWORD'],
    ];

    public function downloadCallback($type, $buffer)
    {
        // $buffer = $this->obj->cleanBuffer($buffer);

        $ConsoleCmd = 'writeln';
        $outputText = '';
        $line_id    = '<id>' . $this->obj->num_of_lines . '</id>';
        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (array_key_exists(2, $matches)) {
                if ($matches[2] != '') {
                    $this->obj->key = $matches[2];
                }
            }
        }

        // if (!str_contains($buffer, '[download]') && !str_contains($buffer, 'ETA')) {
        //     // UTMlog::Logger('Ph Download', $buffer);
        // }
        // // UTMlog::Logger('Ph Download', $buffer);

        MediaFile::file_append_file(__LOGFILE_DIR__ . '/buffer/' . $this->obj->key . '.log', $buffer . PHP_EOL);

        switch ($buffer) {
            case str_starts_with($buffer, '[PornHubPlaylist]'):
                $match = preg_match('/.*PornHubPlaylist.*Downloading \d+ items of (\d+)/', $buffer, $output_array);
                if ($match == true) {
                    $this->obj->num_of_lines = $output_array[1];
                }
                $ConsoleCmd = 'writeln';
                // utmdump($output_array);
                break;

            case str_starts_with($buffer, '[PornHub]'):
                $outputText = $this->obj->Pornhub($buffer, $line_id);
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, 'Interrupted by user'):
                $this->obj->error($buffer, $line_id, 'cancelled');
                $ConsoleCmd = 'writeln';

                return 0;

            case str_contains($buffer, 'private.'):
                $outputText = $this->obj->error($buffer, $line_id, 'private');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'restriction'):
                $outputText = $this->obj->error($buffer, $line_id, 'is restricted ');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, 'disabled'):
                $outputText = $this->obj->error($buffer, $line_id, ' has been disabled ');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, 'HTTPError'):
                $outputText = $this->obj->error($buffer, $line_id, 'NOT FOUND');

                // $this->obj->premiumIds[] = $this->obj->key;

                $this->obj->updateIdList(PlaylistProcess::DISABLED);
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, 'Upgrade now'):
                $outputText = $this->obj->error($buffer, $line_id, ' Premium Video');
                $this->obj->updatePlaylist('premium');
                $this->obj->premiumIds[] = $this->obj->key;
                $ConsoleCmd              = 'writeln';
                break;

            case str_contains($buffer, 'encoded url'):
                $outputText = $this->obj->error($buffer, $line_id, 'ModelHub Video');
                // $this->obj->updatePlaylist('modelhub');
                // $this->obj->updateIdList(PlaylistProcess::MODELHUB);
                $ConsoleCmd = 'writeln';
                break;

            case str_starts_with($buffer, '[info]'):
                if ($this->obj->downloadFiles === false) {
                    $outputText = $this->obj->downloadableIds($buffer);
                }
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, '[download]'):
                $outputText = $this->obj->downloadVideo($buffer, $line_id);
                $ConsoleCmd = 'write';
                break;

            case str_contains($buffer, '[FixupM3u8]'):
                $outputText = $this->obj->fixVideo($buffer, $line_id);
                $ConsoleCmd = 'writeln';
                break;

            case str_contains($buffer, 'ERROR'):
                $outputText = $this->obj->error($buffer, $line_id, 'Uncaught Error </>  <comment>' . $buffer . '</comment><error>');
                // $this->obj->updatePlaylist('error');
                // $this->obj->updateIdList(PlaylistProcess::ERRORIDS);
                $ConsoleCmd = 'writeln';
                break;
        }

        if ($outputText != '') {
            // if (!str_contains($outputText, '<download>')) {
            //     utmdump([$ConsoleCmd, $outputText]);
            // }
            $this->obj->Console->$ConsoleCmd($outputText);
        }
    }
}
