<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Sort;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFinder;
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
    ];

    public $genreDirs = [
        'Bisexual',
        'Compilation',
        'Group',
        'MFF',
        'MMF',
        'Single',
        'Trans',
    ];

    protected $useFuncs = ['addMeta'];

    public $file_array = [];

    private $searchChars = ['__', '-_', '_-', 'Am', 'Pm', '_.', 'MP4'];

    private $replaceChars = ['_', '-', '-', 'AM', 'PM', '.', 'mp4'];

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::boot($input, $output);

        // $this->Search_Array = parent::$finder->Search(null,'/\.mp4$/i', null, false);
    }

    public function exec($option = null)
    {
        $finder             = new MediaFinder;
        $finder->excludeDir = $this->genreDirs;
        $this->file_array   = $finder->search(__PLEX_HOME__ . '/Pornhub/Sort/', '/\.mp4$/i');

        echo $this->sortFiles();
    }
}
