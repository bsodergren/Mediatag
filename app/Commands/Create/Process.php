<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use Lang;
    use MediaProcess;
    use Translate;

    public $defaultCommands = [
        'init' => null,
        'exec' => null,
    ];

    // public $commandList = [

    //     'create'    => ['createCommand' => null],
    //     'add'    => ['addCommand' => null],
    // ];

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        parent::boot($input, $output);
    }
}
