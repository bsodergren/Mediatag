<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsCommand(name: 'update', description: 'Updates metatags on files')]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH=true;

    public $command = [
        'update' => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
        ],
    ];

    // public function cleanOnEvent()
    // {
    //     $this->output->writeln(sprintf('Command <info>%s</info> failed with code <error>%s</error>', $this->getName(), $this->signal));

    //     utmdd('fasdfsda');
    // }
    // public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    // {
    //     utmdd($signal);
    //     // set here any of the constants defined by PCNTL extension
    //     if (in_array($signal, [\SIGINT, \SIGTERM], true)) {
    //         // ...
    //     }

    //     // ...

    //     // set an integer exit code, or
    //     // false to continue normal execution
    //     return 0;
    // }
}
