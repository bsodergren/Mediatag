<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Backup;

use Mediatag\Core\Mediatag;
use Mediatag\Traits\Callables\Callables;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Callables;
    use Helper;
    public $video_array;
    public $backupDirectory;

    public $commandList = [
        'db'        => [
            'backupDb' => null,
        ],
        'backup'    => [
            'exec'         => null,
            'backupStudio' => true,
        ],
        'directory' => [
            'exec'          => null,
            'sortDirectory' => true,
            'backupStudio'  => true,
        ],
        //     'move'          => [
        //         'exec'        => null,
        //         'moveStudios' => null,
        //     ],
        //     'numberofFiles' => [
        //         'exec'             => null,
        //         'getNumberofFiles' => null,
        //     ],
        //     'list'          => [
        //         'exec'        => null,
        //         'getChanges'  => null,
        //         'saveChanges' => 'isset',
        //  ],
    ];

    public $defaultCommands = [
        // 'exec' => null,
        //     'getChanges'   => null,
        //     'writeChanges' => null,
    ];

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        // utminfo(func_get_args());

        parent::boot($input, $output, ['SKIP_SEARCH' => true]);
        //        $this->backupDirectory = $file;
        $this->backupDirectory = __DB_BACKUP_ROOT__.\DIRECTORY_SEPARATOR.$file;
    }

    public function __call($m, $a)
    {
        // utminfo(func_get_args());

        return null;
    }
}
