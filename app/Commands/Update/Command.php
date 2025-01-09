<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;


use UTM\Utilities\Option;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;
use Mediatag\Core\Helper\MediaExecute;

#[AsCommand(name: 'update', description: 'Updates metatags on files')]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;

    public const USE_LIBRARY = true;
}
