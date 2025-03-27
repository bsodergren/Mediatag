<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Mediatag\Core\Mediatag;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Commands\Clip\Commands\Show\ShowHelper;
use Mediatag\Commands\Clip\Commands\Merge\MergeHelper;
use Mediatag\Commands\Clip\Commands\Delete\DeleteHelper;
use Mediatag\Commands\Clip\Commands\Create\CreateHelper;
use Mediatag\Commands\Clip\Commands\Chapter\ChapterHelper;
use Mediatag\Commands\Clip\Commands\Add\AddHelper;

include_once __DATA_MAPS__.'/WordMap.php';

class Process extends Mediatag
{
    use AddHelper;
    use CreateHelper;
    use DeleteHelper;
    use ChapterHelper;

    use Helper;
    use Lang;
    use MediaProcess;
    use MergeHelper;
    use ShowHelper;

    use MediaExecute;


    public $VideoList = [];
    protected $useFuncs = ['addMeta', 'setupMap'];

    public $defaultCommands = [
        // 'exec' => null,
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

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);
        $this->setupFormat();
        $this->setupDb();
    }
}
