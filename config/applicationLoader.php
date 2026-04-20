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

class Factory
{
    /**
     * Create an instance of a class dynamically.
     *
     * @param  string  $className  The name of the class to instantiate.
     * @param  array  $args  Optional arguments to pass to the class constructor.
     * @return object The created instance of the class.
     *
     * @throws Exception if the class does not exist.
     */
    public static function create(string $className, array $args = [])
    {
        if (! class_exists($className)) {
            throw new Exception("Class '$className' does not exist.");
        }

        // Create an instance dynamically with optional constructor arguments
        $reflection = new ReflectionClass($className);

        return $reflection->newInstanceArgs($args);
    }
}

$input  = new ArgvInput;
$output = new ConsoleOutput;

$cmdName = str_replace('media', '', __SCRIPT_NAME__);

$application = new MediaApplication(__SCRIPT_NAME__, '1.0.0');

$commandClasses = [];
$commandsDir    = implode(\DIRECTORY_SEPARATOR, [__APP_HOME__, 'app', 'Commands', ucfirst($cmdName)]);

$default = false;

if (file_exists($commandsDir)) {
    $finder = new Finder;
    $finder->files()->in($commandsDir)->name('*Command.php');
    foreach ($finder as $file) {
        $commandFile = $file->getPathname();
        $files[]     = $commandFile;
        $commandFile = str_replace(__COMMANDS_DIR__, '', $commandFile);

        $commandFileName  = basename($commandFile);
        $CommandClassName = basename($commandFile, '.php');
        $commandFile      = str_replace('/', '\\', $commandFile);
        $commandFile      = str_replace($commandFileName, '', $commandFile);
        $commandClass     = 'Mediatag\\Commands' . ucfirst($commandFile) . '' . $CommandClassName;

        $commandClasses[] = $commandClass;
        if ($CommandClassName == 'Command') {
            $default = true;
        }
    }
}
$singleCommand = false;
if (count($commandClasses) == 1) {
    // $singleCommand = true;
}

$defaultCmd = 'list';

foreach ($commandClasses as $className) {
    $Command = Factory::create($className);
    $application->addCommand($Command);
    if ($Command::$DEFAULT_CMD === true ||
     $singleCommand === true) {
        $defaultCmd = $Command->getName();
    }
}

$application->setDefaultCommand($defaultCmd, $singleCommand);
$application->run();
// $loader = new Nette\DI\ContainerLoader(__PLEX_PL_TMP_DIR__);
// $class  = $loader->load(function ($compiler) {
//     $compiler->loadConfig(__CONFIG_LIB__ . '/config.neon');
// });

// $container = new $class;

// utmdump($container);
