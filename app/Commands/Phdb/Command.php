<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Mediatag\Core\Mediatag;

const DESCRIPTION = 'Example Description';
const NAME        = 'phdb';

use Mediatag\Core\MediaCommand;
use Mediatag\Commands\Phdb\Lang;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;

    public const CMD_NAME        = NAME;
    public const CMD_DESCRIPTION = DESCRIPTION;
    // public const USE_LIBRARY     = false;
    public $process;

    protected $db;
    public const USE_LIBRARY     = false;

    public function execute(InputInterface $input, OutputInterface $output)
    {

        utminfo();
        $phcsv_file         = $input->getArgument(self::CMD_NAME);
        parent::$optionArg  = [$phcsv_file];

        parent::execute($input, $output);
        return SymCommand::SUCCESS;
    }

}
