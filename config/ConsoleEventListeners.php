<?php

/**
 * Command like Metatag writer for video files.
 */

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher;

$dispatcher->addListener(ConsoleEvents::SIGNAL, function (ConsoleSignalEvent $event): void {
    // gets the signal number

    $signal = $event->getHandlingSignal();

    $command         = $event->getCommand();
    $command->output = $event->getOutput();
    $command->signal = $signal;

    if ($signal === \SIGINT) {
        // $event->abortExit();
    }

    $command->cleanOnEvent();

    $event->setExitCode($signal);
    // utmdump(get_class($command));

    exit;
});

$dispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event): void {
    // gets the output

    if ($event->getExitCode() !== 0) {
        return;
    }

    $output          = $event->getOutput();
    $command         = $event->getCommand();
    $command->output = $output;
    // utmdump($event->getExitCode());
    // displays the given content
    // utmdump(get_class($command));
    // $command->cleanOnTerminate();

    // changes the exit code
    $event->setExitCode(128);
});

$dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event): void {
    // gets the input instance
    $input = $event->getInput();

    // gets the output instance
    $output = $event->getOutput();

    // gets the command to be executed
    $command = $event->getCommand();

    // writes something about the command
    // $output->writeln(sprintf('Executing command <info>%s</info>', $command->getName()));

    // gets the application
    // $application = $command->getApplication();
});

$dispatcher->addListener(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event): void {
    $output = $event->getOutput();

    $command = $event->getCommand();

    $output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));

    // gets the current exit code (the exception code)
    $exitCode = $event->getExitCode();

    // changes the exception to another one
    $event->setError(new \LogicException('Caught exception', $exitCode, $event->getError()));
});

return $dispatcher;
