<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Modules\Database\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Mediatag\Commands\Update\Helper as UpdateHelper;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use UpdateHelper;
    use MediaProcess;
     

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList     = [
        'lowercase' => [
            'lowercase' => null,
        ],
        'rename'    => [
            'exec'   => null,
            'renameVids' => null,
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
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        // utminfo();

        parent::boot($input, $output);

        // $this->setupFormat();
        // $this->setupDb();
        // $this->setupMap();

        //        utmdd([__METHOD__,IGNORE_NAME_MAP]);
    }
    // public function exec($option = null)
    // {
    //     // utminfo(func_get_args());

    //     $this->StorageConn = new Storage();

    //     if (!\defined('ARTIST_MAP')) {
    //         $this->getArtistMap('ARTIST_MAP', $this->StorageConn->getArtistMap());
    //     }
    //     if (!\defined('IGNORE_NAME_MAP')) {
    //         $this->getArtistMap('IGNORE_NAME_MAP', $this->StorageConn->getIgnoredArists());
    //     }

    //     //  utmdd([__METHOD__, parent::$SearchArray]);
    // }
}
