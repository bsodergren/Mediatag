<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Mediatag\Core\MediaCommand;
use Mediatag\Commands\Clip\Lang;
use Mediatag\Core\Helper\MediaExecute;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SynCmd;

const DESCRIPTION = 'Clip Command';
const NAME        = 'clip';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;
    public const USE_LIBRARY = true;

   
}
