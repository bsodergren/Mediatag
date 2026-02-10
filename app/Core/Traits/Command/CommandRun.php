<?php

namespace Mediatag\Core\Traits\Command;

use Mediatag\Core\MediaLogger;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;

use function function_exists;
use function is_int;
use function sprintf;

trait CommandRun
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        // self::$Console = new ConsoleOutput($output, $input);
        Mediatag::$log = new MediaLogger($output, $this->getName());

        // add the application arguments and options
        $this->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            if (! $this->ignoreValidationErrors) {
                throw $e;
            }
        }
        $this->initialize($input, $output);

        if ($this->processTitle !== null) {
            if (function_exists('cli_set_process_title')) {
                if (! @cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    } else {
                        cli_set_process_title($this->processTitle);
                    }
                }
            } elseif (function_exists('setproctitle')) {
                setproctitle($this->processTitle);
            } elseif ($output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }

        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }

        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.

        if ($input->hasArgument('command') && $input->getArgument('command') === null) {
            $input->setArgument('command', $this->getName());
        }

        $input->validate();

        $statusCode = 0;
        if ($this->code) {
            //
            $statusCode = ($this->code)($input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
            //  stopwatch();

            if (! is_int($statusCode)) {
                throw new TypeError(
                    sprintf(
                        'Return value of "%s::execute()" must be of the type int, "%s" returned.',
                        /**
                         * unnamed
                         */
                        static::class,
                        get_debug_type($statusCode)
                    )
                );
            }
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }
}
