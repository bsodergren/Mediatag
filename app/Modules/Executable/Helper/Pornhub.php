<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

use const PHP_EOL;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Helper\VideoDownloader;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

use function array_key_exists;

// define('PHP_EOL', '\n' . PHP_EOL);

class Pornhub extends VideoDownloader
{
    public $options = [
        '-o',
        __PLEX_DOWNLOAD__ . '/Pornhub/' . Youtube::__YT_DL_FORMAT__,
        //  '-u',
        //  CONFIG['PH_USERNAME'],
        //  '-p',
        //  CONFIG['PH_PASSWORD'],
    ];

    public $KeyPrefix = 'pornhub';

    public function init($object)
    {
        $this->num_of_lines = $object->num_of_lines;
        //utmdd(get_class_vars(get_class($object)), Option::getValue('max'), $object->num_of_lines);

        $this->registeredbufferFilters = [

            '[PornHubPlaylist]'   => [
                'search'       => [
                    'pattern' => "/.*PornHubPlaylist.*Downloading \d+ items of (\d+)/",
                    'match'   => 1,

                ],
                'ConsoleCmd'   => 'writeln',
                'OutputMethod' => null,
            ],
            '[PornHub]'           => [
                'search'       => 'str_starts_with',
                'ConsoleCmd'   => 'writeln',
                'OutputMethod' => 'Pornhub',
            ],
            'Interrupted by user' => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error'        => ['msg' => 'cancelled'],
                    'updateIdList' => ['args' => 'PlaylistProcess::DISABLED'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            'private'             => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error'        => ['msg' => 'private'],
                    'updateIdList' => ['args' => 'PlaylistProcess::DISABLED'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            'restriction'         => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error'        => ['msg' => 'restriction'],
                    'updateIdList' => ['args' => 'PlaylistProcess::DISABLED'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            'disabled'            => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error'        => ['msg' => 'disabled'],
                    'updateIdList' => ['args' => 'PlaylistProcess::DISABLED'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            'HTTPError'           => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error'        => ['msg' => 'HTTPError'],
                    'updateIdList' => ['args' => 'PlaylistProcess::NOTFOUND'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            'ERROR'               => [
                'search'       => 'str_contains',
                'OutputMethod' => [
                    'error' => ['msg' => 'ERROR'],
                ],
                'ConsoleCmd'   => 'writeLn',
            ],
            '[download]'          => [
                'search'       => 'str_contains',
                'ConsoleCmd'   => 'write',
                'OutputMethod' => 'downloadVideo',
            ],
            '[FixupM3u8]'         => [
                'search'       => 'str_contains',
                'ConsoleCmd'   => 'writeln',
                'OutputMethod' => 'fixVideo',
            ],

            'info'                => [
                'search'       => 'str_starts_with',
                'ConsoleCmd'   => 'writeln',
                'OutputMethod' => 'downloadableIds',
            ],

        ];

        // utmdd($this->registeredbufferFilters);
    }

    public function Pornhub($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = MediatagExec::cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if (str_contains($buffer, 'webpage')) {
            // $this->num_of_lines--;
            $line_id = '<id>' . $this->num_of_lines . '</id>';

            $outputText = $line_id . ' <text>Trying to download  ' . $this->key . '  </text>';
        }

        return $outputText;
    }
}
