<?php
/**
 * Command like Metatag writer for video files.
 */

use Psr\Log\LoggerInterface;
use Mediatag\Core\MediaApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;




$input  = new ArgvInput();
$output = new ConsoleOutput();

$cmdName   = str_replace('media', '', __SCRIPT_NAME__);
$className = 'Mediatag\\Commands\\' . ucfirst($cmdName) . '\\Command';
// $logger    = interface_exists(LoggerInterface::class) ? new ConsoleLogger($output->getErrorOutput()) : null;

// $customCommands = new FactoryCommandLoader([
//     $cmdName      => function () use($className) {return new $className(); },
// ]);

$application = new MediaApplication(__SCRIPT_NAME__, '1.0.0');
$application->add(new $className());
$application->setDefaultCommand($cmdName, true);
$application->run();
