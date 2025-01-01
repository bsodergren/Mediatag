<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use UTM\Utilities\Option;
use Mediatag\Locales\Lang;
use Mediatag\Core\Mediatag;
use Psr\Log\LoggerInterface;
use Mediatag\Traits\Translate;
use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Traits\MediaLibrary;
use UTM\Utilities\Debug\UtmStopWatch;
use Symfony\Component\Console\Application;
use Mediatag\Modules\Display\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Mediatag\Core\MediaInputDefinition as InputDefinition;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class MediaCommand extends MediaDoctrineCommand //implements SignalableCommandInterface
{
    use Lang;
    use MediaLibrary;
    use Translate;



    // public readonly Command $command;
    // public int $exitCode;
    // public ?int $interruptedBySignal = null;
    // public bool $ignoreValidation;
    // public bool $isInteractive    = false;
    // public string $duration       = 'n/a';
    // public string $maxMemoryUsage = 'n/a';
    // public InputInterface $input;
    // public OutputInterface $output;
    // /** @var array<string, mixed> */
    // public array $arguments;
    // /** @var array<string, mixed> */
    // public array $options;
    // /** @var array<string, mixed> */
    // public array $interactiveInputs = [];
    // public array $handledSignals    = [];



    public static $optionArg;

    public static $Console;

    public const USE_LIBRARY = false;

    //    private ?Application $application = null;
    //    private ?string $name = null;
    private ?string $processTitle = null;

    //    private array $aliases = [];
    //    private InputDefinition $definition;
    //    private bool $hidden = false;
    //    private string $help = '';
    //    private string $description = '';
    //   private ?InputDefinition $fullDefinition = null;
    private bool $ignoreValidationErrors = false;

    private ?\Closure $code = null;
    //   private array $synopsis = [];
    //  private array $usages = [];
    //  private ?HelperSet $helperSet = null;

    public static LoggerInterface $logger;

    // public const USE_LIBRARY     = false;

    public function __construct()
    {

    //     //$this->logger = $logger;
        parent::__construct();
    //     self::$logger = $logger;



    }
    public function cleanOnEvent()
    {
        //utmdd(get_class_vars(Mediatag::class));
    }


    public function configure(): void
    {
        // // utminfo(func_get_args());

        $child                      = static::class;
        MediaOptions::$callingClass = $child;
        $this->setName($child::CMD_NAME)->setDescription($child::CMD_DESCRIPTION);
        $this->setDefinition(MediaOptions::getDefinition($this->getName()));

        $arguments = MediaOptions::getArguments($child::CMD_NAME, $child::CMD_DESCRIPTION);
        if (\is_array($arguments)) {
            $this->addArgument(...$arguments);
        }
    }

    /**
     * Runs the command.
     *
     * The code to execute is either defined directly with the
     * setCode() method or by overriding the execute() method
     * in a sub-class.
     *
     * @return int The command exit code
     *
     * @throws ExceptionInterface When input binding fails. Bypass this by calling {@link ignoreValidationErrors()}.
     *
     * @see setCode()
     * @see execute()
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        // utminfo();

        self::$Console = new ConsoleOutput($output, $input);

        // add the application arguments and options
        $this->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }
        $this->initialize($input, $output);

        if (null !== $this->processTitle) {
            if (\function_exists('cli_set_process_title')) {
                if (!@cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === \PHP_OS) {
                        $output->writeln('<comment>Running "cli_set_process_title" as an unprivileged user is not supported on MacOS.</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);
                    } else {
                        cli_set_process_title($this->processTitle);
                    }
                }
            } elseif (\function_exists('setproctitle')) {
                setproctitle($this->processTitle);
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }

        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }

        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }

        $input->validate();

        if ($this->code) {
            $statusCode = ($this->code)($input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
            //  stopwatch();

            if (!\is_int($statusCode)) {
                throw new \TypeError(sprintf('Return value of "%s::execute()" must be of the type int, "%s" returned.', static::class, get_debug_type($statusCode)));
            }
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    public static function getProcessClass()
    {

        // utmdd([__METHOD__,Option::Istrue('completion')]);

        // if (true == Option::isTrue('completion')) {

        //     $greetInput = new ArrayInput([
        //         // the command name is passed as first argument
        //         'command' => 'completion',
        //         'name'    => 'bash',

        //     ]);
        //     utmdd([__METHOD__,$greetInput]);

        //     $returnCode = $obj->getApplication()->doRun($greetInput, $output);
        // }
        // utminfo();
        $className = static::class;
        $classPath = rtrim($className, 'Command');
        $classPath .= 'Process';
        // UTMlog::logger('Process Class', $classPath);

        return $classPath;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        // utminfo();
        $args = [$input, $output];

        Mediatag::$IoStyle = new SymfonyStyle($input, $output);

        if (null !== self::$optionArg) {
            $args = array_merge($args, self::$optionArg);
        }
        $class   = self::getProcessClass();
        $Process = new $class(...$args);
        if (Option::istrue('trunc')) {
            Mediatag::$dbconn->truncate();

            return SymCommand::SUCCESS;
        }
        if (isset(parent::$AskQuestion)) {
            $Process->helper = $this->getHelper(parent::$AskQuestion);
        }

        $Process->process();

        if (null !== $Process->actions) {
            $go       = false;
            $ask      = new QuestionHelper();
            $question = new Question(Translate::text('L__PHDB_ASK_CONTINUE', ['NEXT' => $Process->actions]));

            $answer = $ask->ask($input, $output, $question);

            switch ($answer) {
                case 'y':
                    $go = true;

                    break;

                case 'Y':
                    $go = true;

                    break;

                default:
                    $go = false;

                    break;
            }
            if (true == $go) {
                $greetInput = new ArrayInput([
                    // the command name is passed as first argument
                    'command' => strtolower($Process->actions),
                ]);
                $returnCode = $this->getApplication()->doRun($greetInput, $output);
            }
        }

        return SymCommand::SUCCESS;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        // utminfo();
        $className = static::class;
        Option::init($input);
        UtmStopWatch::init($input, $output);
        if (null !== Option::getValue('path', true)) {
            $path = Option::getValue('path', true);
            chdir($path);
        }
        $this->getLibrary($className::USE_LIBRARY);

        $this->loadDirs();
    }

    protected function loadDirs()
    {

        $filesystem = new Filesystem();
        foreach (__CREATE_DIRS__ as $dir) {
            if (!is_dir($dir)) {
                $filesystem->mkdir($dir);
            }

        }
    }
}
