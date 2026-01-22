<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

use Mediatag\Commands\Playlist\Process as PlaylistProcess;
use Mediatag\Modules\Executable\Callbacks\YtdlpCallBacks;
use Mediatag\Modules\Executable\Helper\VideoDownloader;
use Mediatag\Modules\Executable\MediatagExec;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

use function array_key_exists;

class Studio extends VideoDownloader
{
    public $options = [
        '-o',
        __PLEX_DOWNLOAD__ . '/Nubiles/' . Youtube::__YT_DL_FORMAT__,
        '-u',
        CONFIG['NUB_USERNAME'],
        '-p',
        CONFIG['NUB_PASSWORD'],
    ];

    public $KeyPrefix = 'nubilesporn';

    public function init($object)
    {
        $this->num_of_lines = $object->num_of_lines;
        //utmdd(get_class_vars(get_class($object)), Option::getValue('max'), $object->num_of_lines);

        $this->registeredbufferFilters = [

            '[NubilesPorn]'       => [
                'search'       => 'str_starts_with',
                'ConsoleCmd'   => 'writeln',
                'OutputMethod' => 'NubilesPorn',
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
                    'updateIdList' => ['args' => 'PlaylistProcess::DISABLED'],
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

    public function NubilesPorn($buffer, $line_id)
    {
        $outputText = '';
        $buffer     = MediatagExec::cleanBuffer($buffer);

        PlaylistProcess::$current_key = false;
        if (str_contains($buffer, $this->key . ': Downloading')) {
            // $this->num_of_lines--;
            $line_id = '<id>' . $this->num_of_lines . '</id>';

            $outputText = $line_id . ' <text>Trying to download  ' . $this->key . '  </text>';
        }

        return $outputText;
    }
}
