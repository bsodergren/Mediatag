<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use MediaExecute;
    use MediaProcess;

    public $VideoList;

    public $defaultCommands = [
        // 'init' => null,
        'exec' => null,
    ];

    public $commandList = [
        'lowercase' => [
            'lowercase' => null,
        ],
        'trans'     => [
            'translate' => true,
        ],
    ];

    protected $useFuncs = ['addMeta', 'setupMap'];

    public $genrePath = [];

    private $searchChars = ['__', '-_', '_-', 'Am', 'Pm', '_.', 'MP4'];

    private $replaceChars = ['_', '-', '-', 'AM', 'PM', '.', 'mp4'];

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::boot($input, $output);
    }

    // public function exec($option = null)
    // {
    //     // utminfo(func_get_args());

    //     $this->getFileArray();
    //     // $this->VideoList  = parent::$SearchArray;
    //     //  $this->execUpdate();
    // }
}
