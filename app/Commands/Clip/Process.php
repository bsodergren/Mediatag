<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Commands\Clip\Commands\Create\CreateHelper;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\DynamicProperty;

include_once __DATA_MAPS__ . '/WordMap.php';

class Process extends Mediatag
{
    use CreateHelper;
    use DynamicProperty;

    // use AddHelper;
    // use ChapterHelper;
    // use CreateHelper;
    // use DeleteHelper;
    use Helper;
    use Lang;
    use MediaExecute;
    use MediaProcess;
    // use MergeHelper;
    // use ShowHelper;

    public $dbConn;

    protected $useFuncs = ['addMeta'];

    public $db_array = [];

    public $file_array = [];

    public $Search_Array = null;

    public $read;

    public $meta;

    public $OutputText = [];

    public $New_Array = [];

    public $Deleted_Array = [];

    public $Changed_Array = [];

    public $allDbFiles = [];

    public $defaultCommands = [
        // 'init' => null,
        // 'exec' => null,
    ];

    public $commandList = [

    ];

    private $count;

    public object $DbMap;

    private $thumb;

    private $vinfo;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::boot($input, $output);
        $this->dbConn = new Storage;
        //
        //parent::$SearchArray;
    }

    public $VideoList = [];

    // protected $useFuncs = ['addMeta', 'setupMap'];

    // public $defaultCommands = [
    //     // 'exec' => null,
    // ];

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

    // public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    // {
    //     parent::boot($input, $output);

    //     // $this->setupFormat();
    //     // $this->setupDb();
    // }
}
