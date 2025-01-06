<?php
/**
 * Command like Metatag writer for video files.
 */
use Nette\Utils\Callback;

use Psr\Log\LoggerInterface;
// use Symfony\Component\Console\ConsoleEvents;
use Mediatag\Core\MediaApplication;
// use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
// use Symfony\Component\EventDispatcher\EventDispatcher;
// use Symfony\Component\Console\Event\ConsoleSignalEvent;
// use Symfony\Component\Console\Event\ConsoleCommandEvent;
// use Symfony\Component\Console\Event\ConsoleTerminateEvent;
// use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;




$input  = new ArgvInput();
$output = new ConsoleOutput();


$application = new MediaApplication(__SCRIPT_NAME__, '1.0.0');


$cmdName   = str_replace('media', '', __SCRIPT_NAME__);

$commandClasses = [];
$commandsDir = implode(DIRECTORY_SEPARATOR,[__APP_HOME__ , 'app','Commands',ucfirst($cmdName) ] );

$default = false;

if(file_exists($commandsDir)) {
    $finder = new Finder();
    $finder->files()->in($commandsDir)->name("*Command.php");
    foreach ($finder as $file) {
        $CommandClassName =  basename($file->getPathname(),'.php');
        $commandClasses[] = 'Mediatag\\Commands\\' . ucfirst($cmdName) . '\\' .$CommandClassName;
        if($CommandClassName == "Command"){
            $default = true;        
        }
    }
} 
$SingleCommand = false;

if(count($commandClasses) == 1){
    $default = true;
    $SingleCommand = true;
}

foreach($commandClasses as $className)
{
    $Command = new $className();
    $application->add($Command);
}

if($default === true) {
   
    $application->setDefaultCommand($cmdName, $SingleCommand);
}
$application->run();
