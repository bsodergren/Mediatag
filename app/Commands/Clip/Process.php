<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Commands\Clip\Lang;
use Mediatag\Commands\Clip\Helper;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Modules\Display\ShowDisplay;

use Mediatag\Commands\Clip\Commands\Add\AddHelper;
use Symfony\Component\Console\Input\InputInterface;
use Mediatag\Commands\Clip\Commands\Show\ShowHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Mediatag\Commands\Clip\Commands\Merge\MergeHelper;
use Mediatag\Commands\Clip\Commands\Create\CreateHelper;
use Mediatag\Commands\Clip\Commands\Delete\DeleteHelper;

include_once __DATA_MAPS__.'/WordMap.php';

class Process extends Mediatag
{
    use Helper;
    use Lang;
    use MediaProcess;

    use MergeHelper;
    use CreateHelper;
    use DeleteHelper;
    use AddHelper;
    use ShowHelper;

    public $VideoList = [];

    public $defaultCommands = [
        'exec' => null,
    ];

    // public $commandList = [
    //     'merge'           => [
    //         'exec'            => null,
    //         'getfileList'     => null,
    //         'mergeClips'      => null,
    //     ],
    //     'create'          => [
    //         'exec'         => null,
    //         'getfileList'  => null,
    //         'createClips'  => null,
    //     ],
    //     'delete'          => [
    //         'deleteClips' => null,
    //     ],
    //     'add'             => [
    //         'exec'        => null,
    //         'addMarker'   => null,
    //     ],
    // ];

    public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
       
        parent::boot($input, $output);
        $this->setupFormat();
        $this->setupDb();
    }
}
