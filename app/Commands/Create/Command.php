<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\Mediatag;

use Nette\Utils\FileSystem;
use Mediatag\Core\MediaCommand;
use Mediatag\Traits\CmdCreater;
use Mediatag\Traits\MediaLibrary;
use Mediatag\Core\Helper\MediaExecute;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

#[AsCommand(name: 'create', description: 'Create a new Command')]
final class Command extends MediaCommand
{
    use Lang;
     
    public const USE_LIBRARY = false;
    public const SKIP_SEARCH = true;



    public $command = [

        'create'    => ['createCommand' => null],
    ];


}
