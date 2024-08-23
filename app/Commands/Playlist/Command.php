<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

use Mediatag\Core\Mediatag;

const DESCRIPTION = 'download PH Playlist';
const NAME        = 'playlist';

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;

    public const CMD_NAME        = NAME;
    public const CMD_DESCRIPTION = DESCRIPTION;
    // public const USE_LIBRARY     = false;
    public $process;

    protected $db;

    public function handleSignal(int $signal): void
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (\SIGINT === $signal) {
            echo \PHP_EOL;
            echo 'Exiting, cleaning up';
            echo \PHP_EOL;
            Process::Cleanup();

            exit;
        }
    }

    /**
     * Method execute.
     *
     * @param InputInterface  $input  [explicite description]
     * @param OutputInterface $output [explicite description]
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $playlist          = $input->getArgument(self::CMD_NAME);
        if ($playlist === null) {
            $playlist = Option::getValue('playlist');
        }

        parent::$optionArg = [$playlist];
        parent::execute($input, $output);

        return SymCommand::SUCCESS;
    }
}
