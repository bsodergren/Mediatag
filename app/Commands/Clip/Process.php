<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Commands\Clip\Commands\Add\AddHelper;
use Mediatag\Commands\Clip\Commands\Chapter\ChapterHelper;
use Mediatag\Commands\Clip\Commands\Create\CreateHelper;
use Mediatag\Commands\Clip\Commands\Delete\DeleteHelper;
use Mediatag\Commands\Clip\Commands\Merge\MergeHelper;
use Mediatag\Commands\Clip\Commands\Resize\ResizeHelper;
use Mediatag\Commands\Clip\Commands\Show\ShowHelper;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once __DATA_MAPS__ . '/WordMap.php';

class Process extends Mediatag
{
    use AddHelper;
    use ChapterHelper;
    use CreateHelper;
    use DeleteHelper;
    use Helper;
    use Lang;
    use MediaExecute;
    use MediaProcess;
    use MergeHelper;
    use ResizeHelper;
    use ShowHelper;

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
