<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Backup;

use Mediatag\Core\Mediatag;



use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

const DESCRIPTION = 'backup Database';
const NAME        = 'backup';
#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;
    public const USE_LIBRARY = false;

    public function execute(InputInterface $input, OutputInterface $output): int
    {

        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $backupDirectory[] = $input->getArgument(self::CMD_NAME);
        parent::$optionArg = $backupDirectory;
        parent::execute($input, $output);

        return SymCommand::SUCCESS;
    }
}
