<?php

namespace Mediatag\Controller;

use JBZoo\Cli\CliApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$input  = new ArgvInput;
$output = new ConsoleOutput;

$cmdName = str_replace('media', '', __SCRIPT_NAME__);

// Scan directory to find commands.
//  * It doesn't work recursively!
//  * They must be inherited from the class \JBZoo\Cli\CliCommand
$commandsDir = implode(\DIRECTORY_SEPARATOR, [dirname(__DIR__), 'app', 'Controller', ucfirst($cmdName), 'Commands']);

$application = new CliApplication('My Console Application', 'v1.0.0');

// Looks at the online generator of ASCII logos
// https://patorjk.com/software/taag/#p=testall&f=Epic&t=My%20Console%20App
$application->setLogo(
    <<<'EOF'
          __  __          _____                      _
         |  \/  |        / ____|                    | |          /\
         | \  / |_   _  | |     ___  _ __  ___  ___ | | ___     /  \   _ __  _ __
         | |\/| | | | | | |    / _ \| '_ \/ __|/ _ \| |/ _ \   / /\ \ | '_ \| '_ \
         | |  | | |_| | | |___| (_) | | | \__ \ (_) | |  __/  / ____ \| |_) | |_) |
         |_|  |_|\__, |  \_____\___/|_| |_|___/\___/|_|\___| /_/    \_\ .__/| .__/
                  __/ |                                               | |   | |
                 |___/                                                |_|   |_|
        EOF,
);
$application->registerCommandsByPath($commandsDir, __NAMESPACE__, true);
$application->setDefaultCommand($application->defaultCmd);

// Run application
$application->run();
