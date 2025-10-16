<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use const PHP_OS;

use Closure;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Mediatag\Core\Helper\CommandHelper;
use Mediatag\Locales\Lang;
use Mediatag\Modules\Display\ConsoleOutput;
use Mediatag\Traits\MediaLibrary;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;
use UTM\Utilities\Option;

use function array_key_exists;
use function call_user_func;
use function count;
use function function_exists;
use function is_array;
use function is_int;
use function sprintf;

class MediaCommand extends DoctrineCommand
{
    use CommandHelper;
    use Lang;
    use MediaLibrary;
    use Translate;

    public static $Console;

    public const USE_LIBRARY = false;

    public const SKIP_SEARCH = false;

    public static $SingleCommand = false;

    public $command = [];

    private ?string $processTitle = null;

    private bool $ignoreValidationErrors = false;

    private ?Closure $code = null;

    public static $optionArg = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Mediatag::$ProcessHelper = $this->getHelper('process');

        if (Option::istrue('trunc')) {
            Mediatag::$dbconn->truncate();

            return Command::SUCCESS;
        }

        Mediatag::$log->debug('Command Class {cls}', ['cls' => $output]);

        $class     = static::class;
        $arguments = $input->getArguments();

        if (count($arguments) > 0) {
            $cmdArgument = $input->getArgument($this->getName());

            if ($cmdArgument !== null) {
                self::$optionArg = array_merge(self::$optionArg, [$cmdArgument]);
            }
        }

        $class = self::getProcessClass();
        Mediatag::$log->info('Command arguments {arguments} for {class}', ['arguments' => $arguments,
            'class'                                                                    => $class]);
        $Process = new $class($input, $output, self::$optionArg);

        Mediatag::$log->info('Command arguments {Process} for {command}', ['Process' => $Process->commandList,
            'command'                                                                => $this->command]);

        $Process->commandList = array_merge($Process->commandList, $this->command);

        $method = 'process';
        if (array_key_exists('command', $arguments)) {
            $method = $arguments['command'];
        }

        $Process->$method();

        return Command::SUCCESS;
    }

    public function cleanOnEvent()
    {
        // utmdd(get_class_vars(Mediatag::class));
    }

    public function configure(): void
    {
        $child                      = static::class;
        MediaOptions::$callingClass = $child;

        $this->setDefinition(MediaOptions::getDefinition($this->getName()));
        $arguments = MediaOptions::getArguments(
            $this->getName(),
            $this->getDescription(),
            function (CompletionInput $input) {
                return call_user_func([MediaOptions::$CmdClass, 'ArgumentClosure'], $input, $this->getName());
            }
        );

        if (is_array($arguments)) {
            $this->addArgument(...$arguments);
        }
    }

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

        if ($this->code) {
            utmdd($this->code);
            $statusCode = ($this->code)($input, $output);
        } else {
            $statusCode = $this->execute($input, $output);

            //  stopwatch();

            if (! is_int($statusCode)) {
                throw new TypeError(sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class, get_debug_type($statusCode)));
            }
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // utminfo();
        $className = static::class;
        Option::init($input);
        if (Option::getValue('path', true) !== null) {
            $path = Option::getValue('path', true);
            chdir($path);
        }

        $this->getLibrary($className::USE_LIBRARY);

        Mediatag::$log->info('Init vars {library}', ['library' => __LIBRARY__]);
        Option::set('SKIP_SEARCH', $className::SKIP_SEARCH);

        $this->loadDirs();
    }
}
