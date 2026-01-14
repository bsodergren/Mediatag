<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use const PHP_EOL;

use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Application;
// use Mediatag\Core\Helper\MediaOutputInterface as OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class MediaApplication extends Application
{
    use Lang;
    use Translate;

    protected function getDefaultInputDefinition(): InputDefinition
    {
        Translate::$Class = __CLASS__;

        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, Translate::text('L__APP_DEFAULT_CMD') . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_HELP')),
            new InputOption('--quiet', '-q|qq|qqq', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_QUIET')),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERBOSE')),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_VERSION')),
            new InputOption('--ansi', '', InputOption::VALUE_NEGATABLE, 'Force (or disable --no-ansi) ANSI output', null),
            new InputOption('--no-interaction', '', InputOption::VALUE_NONE, Translate::text('L__APP_DEFAULT_NOASK')),
            new InputOption('--path', '', InputOption::VALUE_REQUIRED, Translate::text('L__APP_DEFAULT_PATH')),
        ]);
    }
    /**
     * Configures the input and output instances based on the user arguments and options.
     */
    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        if ($input->hasParameterOption(['--ansi'], true)) {
            $output->setDecorated(true);
        } elseif ($input->hasParameterOption(['--no-ansi'], true)) {
            $output->setDecorated(false);
        }

        $shellVerbosity = match (true) {
            $input->hasParameterOption(['--silent'], true) => -4,
            $input->hasParameterOption('-qqq', true) || $input->hasParameterOption('--quiet=3', true) || -3 === $input->getParameterOption('--quiet', false, true) => -3,
            $input->hasParameterOption('-qq', true) || $input->hasParameterOption('--quiet=2', true) || -2 === $input->getParameterOption('--quiet', false, true) => -2,
            $input->hasParameterOption('-q', true) || $input->hasParameterOption('--quiet=1', true) || $input->hasParameterOption('--quiet', true) || -1 === $input->getParameterOption('--quiet', false, true) => -1,

            // $input->hasParameterOption(['--quiet', '-q'], true) => -1,
            $input->hasParameterOption('-vvv', true) || $input->hasParameterOption('--verbose=3', true) || 3 === $input->getParameterOption('--verbose', false, true) => 3,
            $input->hasParameterOption('-vv', true) || $input->hasParameterOption('--verbose=2', true) || 2 === $input->getParameterOption('--verbose', false, true) => 2,
            $input->hasParameterOption('-v', true) || $input->hasParameterOption('--verbose=1', true) || $input->hasParameterOption('--verbose', true) || $input->getParameterOption('--verbose', false, true) => 1,
            default => (int) ($_ENV['SHELL_VERBOSITY'] ?? $_SERVER['SHELL_VERBOSITY'] ?? getenv('SHELL_VERBOSITY')),
        };


        $output->setVerbosity(match ($shellVerbosity) {
            -1 => OutputInterface::VERBOSITY_QUIET,
            -2 => OutputInterface::VERBOSITY_SILENT,
            -4 => OutputInterface::VERBOSITY_VERY_QUIET,
            -3 => OutputInterface::VERBOSITY_VERY_VERY_QUIET,
            1 => OutputInterface::VERBOSITY_VERBOSE,
            2 => OutputInterface::VERBOSITY_VERY_VERBOSE,
            3 => OutputInterface::VERBOSITY_DEBUG,
            default => ($shellVerbosity = 0) ?: $output->getVerbosity(),
        });

        

        if (0 > $shellVerbosity || $input->hasParameterOption(['--no-interaction', '-n'], true)) {
            $input->setInteractive(false);
        }

        if (\function_exists('putenv')) {
            @putenv('SHELL_VERBOSITY=' . $shellVerbosity);
        }
        $_ENV['SHELL_VERBOSITY']    = $shellVerbosity;
        $_SERVER['SHELL_VERBOSITY'] = $shellVerbosity;
    }

}
