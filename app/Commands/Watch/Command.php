<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Watch;

use Mediatag\Core\MediaCommand;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

const DESCRIPTION = 'Watch Command';
const NAME        = 'watch';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    //     protected function execute(InputInterface $input, OutputInterface $output): int
    //     {
    // //        Mediatag::$output->write('Updating timestamp... ');

    //         $output->writeln('Updating timestamp...  ' . time());
    //  sleep(2);

    //     }
}
