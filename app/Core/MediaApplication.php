<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\Service\ResetInterface;

class MediaApplication extends Application implements ResetInterface
{
    use Lang;
    use Translate;

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[]
     */
    // protected function getDefaultCommands(): array
    // {
    //     return [
    //         new HelpCommand,
    //         new ListCommand,
    //         new CompleteCommand,
    //     ];
    // }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        self::$Class = __CLASS__;

        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>' . $this->getName() . '</info> command'),
            new InputOption('--path', '', InputOption::VALUE_REQUIRED, self::text('L__APP_DEFAULT_PATH')),
            new InputOption('--silent', null, InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Only errors are displayed. All other output is suppressed'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ]);
    }

    /**
     * Configures the input and output instances based on the user arguments and options.
     */
    // protected function configureIO(InputInterface $input, OutputInterface $output): void
    // {
    //     if ($input->hasParameterOption(['--ansi'], true)) {
    //         $output->setDecorated(true);
    //     } elseif ($input->hasParameterOption(['--no-ansi'], true)) {
    //         $output->setDecorated(false);
    //     }

    //     $shellVerbosity = match (true) {
    //         $input->hasParameterOption(['--silent'], true)                                                                                                                                                      => -4,
    //         $input->hasParameterOption('-qqq', true) || $input->hasParameterOption('--quiet=3', true) || $input->getParameterOption('--quiet', false, true) === -3                                              => -3,
    //         $input->hasParameterOption('-qq', true) || $input->hasParameterOption('--quiet=2', true) || $input->getParameterOption('--quiet', false, true) === -2                                               => -2,
    //         $input->hasParameterOption('-q', true) || $input->hasParameterOption('--quiet=1', true) || $input->hasParameterOption('--quiet', true) || $input->getParameterOption('--quiet', false, true) === -1 => -1,

    //         // $input->hasParameterOption(['--quiet', '-q'], true) => -1,
    //         $input->hasParameterOption('-vvv', true) || $input->hasParameterOption('--verbose=3', true) || $input->getParameterOption('--verbose', false, true) === 3                                           => 3,
    //         $input->hasParameterOption('-vv', true) || $input->hasParameterOption('--verbose=2', true) || $input->getParameterOption('--verbose', false, true) === 2                                            => 2,
    //         $input->hasParameterOption('-v', true) || $input->hasParameterOption('--verbose=1', true) || $input->hasParameterOption('--verbose', true) || $input->getParameterOption('--verbose', false, true)  => 1,
    //         default                                                                                                                                                                                             => (int) ($_ENV['SHELL_VERBOSITY'] ?? $_SERVER['SHELL_VERBOSITY'] ?? getenv('SHELL_VERBOSITY')),
    //     };

    //     $output->setVerbosity(match ($shellVerbosity) {
    //         -1      => OutputInterface::VERBOSITY_QUIET,
    //         -2      => OutputInterface::VERBOSITY_SILENT,
    //         -4      => OutputInterface::VERBOSITY_VERY_QUIET,
    //         -3      => OutputInterface::VERBOSITY_VERY_VERY_QUIET,
    //         1       => OutputInterface::VERBOSITY_VERBOSE,
    //         2       => OutputInterface::VERBOSITY_VERY_VERBOSE,
    //         3       => OutputInterface::VERBOSITY_DEBUG,
    //         default => ($shellVerbosity = 0) ?: $output->getVerbosity(),
    //     });

    //     if ($shellVerbosity < 0 || $input->hasParameterOption(['--no-interaction', '-n'], true)) {
    //         $input->setInteractive(false);
    //     }

    //     if (\function_exists('putenv')) {
    //         @putenv('SHELL_VERBOSITY=' . $shellVerbosity);
    //     }
    //     $_ENV['SHELL_VERBOSITY']    = $shellVerbosity;
    //     $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    // }
}
