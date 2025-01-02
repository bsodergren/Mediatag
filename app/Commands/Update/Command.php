<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;

const DESCRIPTION = 'Updates metatags on files';
const NAME        = 'update';

use UTM\Utilities\Option;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;
use Mediatag\Core\Helper\MediaExecute;

#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;

    public const USE_LIBRARY = true;
}
