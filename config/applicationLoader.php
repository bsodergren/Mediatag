<?php
/**
 * Command like Metatag writer for video files.
 */

// use Symfony\Component\Console\ConsoleEvents;
// use Symfony\Component\Console\Logger\ConsoleLogger;
use Mediatag\Core\MediaApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;

// use Symfony\Component\EventDispatcher\EventDispatcher;
// use Symfony\Component\Console\Event\ConsoleSignalEvent;
// use Symfony\Component\Console\Event\ConsoleCommandEvent;
// use Symfony\Component\Console\Event\ConsoleTerminateEvent;
// use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

$input  = new ArgvInput();
$output = new ConsoleOutput();

$cmdName = str_replace('media', '', __SCRIPT_NAME__);

$application = new MediaApplication(__SCRIPT_NAME__, '1.0.0');

$commandClasses = [];
$commandsDir    = implode(\DIRECTORY_SEPARATOR, [__APP_HOME__, 'app', 'Commands', ucfirst($cmdName)]);

$default = false;

if (file_exists($commandsDir)) {
    $finder = new Finder();
    $finder->files()->in($commandsDir)->name('*Command.php');
    foreach ($finder as $file) {
        $commandFile = $file->getPathname();
        $files[]     = $commandFile;
        $commandFile = str_replace(__COMMANDS_DIR__, '', $commandFile);

        $commandFileName  = basename($commandFile);
        $CommandClassName = basename($commandFile, '.php');
        $commandFile      = str_replace('/', '\\', $commandFile);
        $commandFile      = str_replace($commandFileName, '', $commandFile);
        $commandClass     = 'Mediatag\\Commands'.ucfirst($commandFile).''.$CommandClassName;

        $commandClasses[] = $commandClass;
        if ('Command' == $CommandClassName) {
            $default = true;
        }
    }
}

$SingleCommand = false;
if (1 == count($commandClasses)) {
    $default       = true;
    $SingleCommand = true;
}

foreach ($commandClasses as $className) {
    $Command = new $className();
    $application->add($Command);
}

if (true === $default) {
    $application->setDefaultCommand($cmdName, $SingleCommand);
}

$application->run();
