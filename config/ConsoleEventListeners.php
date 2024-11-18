<?php 

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

$dispatcher = new EventDispatcher();
    
$dispatcher->addListener(ConsoleEvents::SIGNAL, function (ConsoleSignalEvent $event): void {

    // gets the signal number
    $signal = $event->getHandlingSignal();
    $command = $event->getCommand();

    // sets the exit code
    $event->setExitCode(0);
   
 $command->cleanOnEvent();
    if (\SIGINT === $signal) {

        echo "bye bye!";
    }

    utmdd(get_class( $command));

    exit;


});

$dispatcher->addListener(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event): void {
    // gets the output
    $output = $event->getOutput();

    // gets the command that has been executed
    $command = $event->getCommand();

    // displays the given content
    $command->cleanOnTerminate();
    $output->writeln(sprintf('<info>%s</info> Completed', $command->getName()));


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
    $output->writeln(sprintf('Executing command <info>%s</info>', $command->getName()));

    // gets the application
   // $application = $command->getApplication();
});


return $dispatcher;