<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SynCmd;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use UTM\Utilities\Option;

const DESCRIPTION = 'Test Command';
const NAME        = 'test';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
   
}
