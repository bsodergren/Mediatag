<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Executable\Callbacks\YtdlpCallBacks;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

class Pornhub
{
    use YtdlpCallBacks;

    public $options = [
        '-o',
        __PLEX_DOWNLOAD__.'/Pornhub/'.Youtube::__YT_DL_FORMAT__,
        '-u',
        CONFIG['PH_USERNAME'],
        '-p',
        CONFIG['PH_PASSWORD'],
    ];

    public $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function downloadCallback($type, $buffer)
    {
        // $buffer = $this->obj->cleanBuffer($buffer);

        $outputText = '';
        $line_id    = '<id>'.$this->obj->num_of_lines.'</id>';
        if (preg_match('/(ERROR|\[.*\]):?\s+([a-z0-9]+):\s+(.*)/', $buffer, $matches)) {
            if (\array_key_exists(2, $matches)) {
                if ('' != $matches[2]) {
                    $this->obj->key = $matches[2];
                }
            }
        }

        // if (!str_contains($buffer, '[download]') && !str_contains($buffer, 'ETA')) {
        //     // UTMlog::Logger('Ph Download', $buffer);
        // }
        // // UTMlog::Logger('Ph Download', $buffer);

        // MediaFile::file_append_file(__LOGFILE_DIR__ . "/buffer/" . $this->obj->key . ".log", $buffer . PHP_EOL);

        switch ($buffer) {

            case str_starts_with($buffer, '[PornHubPlaylist]'):
                $match = preg_match('/.*PornHubPlaylist.*Downloading \d+ items of (\d+)/', $buffer, $output_array);
                if ($match == true) {
                    $this->obj->num_of_lines = $output_array[1];
                }

                utmdump($output_array);
                break;


            case str_starts_with($buffer, '[PornHub]'):
                $outputText = $this->obj->Pornhub($buffer, $line_id);
                break;

            case str_contains($buffer, 'Interrupted by user'):
                $this->obj->error($buffer, $line_id, 'cancelled');

                return 0;

            case str_contains($buffer, 'private.'):
                $outputText = $this->obj->error($buffer, $line_id, 'private');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'restriction'):
                $outputText = $this->obj->error($buffer, $line_id, 'is restricted ');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);
                break;

            case str_contains($buffer, 'disabled'):
                $outputText = $this->obj->error($buffer, $line_id, ' has been disabled ');
                $this->obj->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'HTTPError'):
                $outputText = $this->obj->error($buffer, $line_id, 'NOT FOUND');

                // $this->obj->premiumIds[] = $this->obj->key;

                $this->obj->updateIdList(PlaylistProcess::DISABLED);

                break;

            case str_contains($buffer, 'Upgrade now'):
                $outputText = $this->obj->error($buffer, $line_id, ' Premium Video');
                $this->obj->updatePlaylist('premium');
                $this->obj->premiumIds[] = $this->obj->key;

                break;

            case str_contains($buffer, 'encoded url'):
                $outputText = $this->obj->error($buffer, $line_id, 'ModelHub Video');
                // $this->obj->updatePlaylist('modelhub');
                // $this->obj->updateIdList(PlaylistProcess::MODELHUB);

                break;

            case str_starts_with($buffer, '[info]'):
                if (false === $this->obj->downloadFiles) {
                    $outputText = $this->obj->downloadableIds($buffer);
                }
                break;

            case str_contains($buffer, '[download]'):
                $outputText = $this->obj->downloadVideo($buffer, $line_id);

                break;

            case str_contains($buffer, '[FixupM3u8]'):
                $outputText = $this->obj->fixVideo($buffer, $line_id);

                break;

            case str_contains($buffer, 'ERROR'):
                $outputText = $this->obj->error($buffer, $line_id, 'Uncaught Error </>  <comment>'.$buffer.'</comment><error>');
                // $this->obj->updatePlaylist('error');
                // $this->obj->updateIdList(PlaylistProcess::ERRORIDS);

                break;
        }

        // if (Option::istrue('debug')) {
        //     $style     = 'info';
        //     $style_end = 'info';
        //     if (preg_match('/(ERROR):(.*)/', $buffer, $matches)) {
        //         $style     = 'fg=bright-magenta';
        //         $style_end = '';
        //     }
        //     $outputText = __LINE__ . '<comment>' . $this->obj->num_of_lines . '</comment> <' . $style . '>' . $buffer . '</' . $style_end . '>' ;
        // }
        if ('' != $outputText) {
            $this->obj->Console->write($outputText);
        }
    }
}
