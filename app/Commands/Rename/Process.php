<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\Mediatag;


use Mediatag\Commands\Update\Helper as UpdateHelper;
use Mediatag\Modules\Database\Storage;

class Process extends Mediatag
{
    use Helper;
    use UpdateHelper;

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList     = [
        'lowercase' => [
            'lowercase' => null,
        ],
        'rename'    => [
            'exec'   => null,
            'rename' => null,
        ],
        'trans'     => [
            'translate' => true,
        ],
        'move'      => [
            'moveStudios' => null,
            'prunedirs'   => null,
        ],
    ];

    public $genrePath       = [];

    private $searchChars    = ['__', '-_', '_-', 'Am', 'Pm', '_.', 'MP4'];

    private $replaceChars   = ['_', '-', '-', 'AM', 'PM', '.', 'mp4'];

    public function exec($option = null)
    {
        utminfo(func_get_args());

        $this->StorageConn = new Storage();

        if (!\defined('ARTIST_MAP')) {
            $this->getArtistMap('ARTIST_MAP', $this->StorageConn->getArtistMap());
        }
        if (!\defined('IGNORE_NAME_MAP')) {
            $this->getArtistMap('IGNORE_NAME_MAP', $this->StorageConn->getIgnoredArists());
        }

        //  utmdd([__METHOD__, parent::$SearchArray]);
    }
}
