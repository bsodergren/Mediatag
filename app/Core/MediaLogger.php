<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use const FILE_APPEND;
use const PHP_EOL;

use DateTimeInterface;
use Mediatag\Core\Helper\LogFormat;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use UTM\Utilities\Colors;
use UTM\Utilities\Debug\Debug;
use UTM\Utilities\Debug\PrettyArray;

use function is_object;
use function is_scalar;
use function sprintf;

class MediaLogger extends ConsoleLogger implements LoggerInterface
{
    use LogFormat;

    public const INFO = 'info';

    public const ERROR = 'error';

    public const DEBUG = 'debug';

    public const NOTICE = 'playlist';

    public const WARNING = 'playlist';

    private $backtrace = '';

    // private $log;
    public static $logger;

    public static $USE_DEBUG = true;

    public static $pruneLogs = false;

    private bool $errored = false;

    private $colors;

    private $output;

    private $dumper;

    private $cloner;

    private $channel = 'default';

    private $logfile;

    // private $logfile;
    private array $verbosityLevelMap = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE    => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::INFO      => OutputInterface::VERBOSITY_VERY_VERBOSE,
        LogLevel::DEBUG     => OutputInterface::VERBOSITY_DEBUG,
    ];

    private array $logVerbosityLevelMap = [
        // LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::INFO   => OutputInterface::VERBOSITY_VERY_VERBOSE,
        // LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR    => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE   => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::INFO     => OutputInterface::VERBOSITY_QUIET,
        LogLevel::DEBUG    => OutputInterface::VERBOSITY_VERY_VERBOSE,
    ];

    private array $ColorLevelMap = [
        LogLevel::DEBUG    => 'yellow',
        LogLevel::INFO     => 'green',
        LogLevel::NOTICE   => 'blue',
        LogLevel::ERROR    => 'red',
        LogLevel::CRITICAL => 'orange',
    ];

    private array $formatLevelMap = [
        LogLevel::EMERGENCY => self::ERROR,
        LogLevel::ALERT     => self::ERROR,
        LogLevel::CRITICAL  => self::ERROR,
        LogLevel::ERROR     => self::ERROR,
        LogLevel::WARNING   => self::WARNING,
        LogLevel::NOTICE    => self::NOTICE,
        LogLevel::INFO      => self::INFO,
        LogLevel::DEBUG     => self::DEBUG,
    ];

    public function __construct($output, $channel = 'default')
    {
        $this->channel = $channel;

        $this->colors = new Colors;
        // $this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
        // $this->formatLevelMap    = $formatLevelMap    + $this->formatLevelMap;

        // // utmdump([$this->verbosityLevelMap, $this->formatLevelMap]);
        parent::__construct($output, $this->verbosityLevelMap, $this->formatLevelMap);
        //        $log = new ConsoleLogger($output);//, $this->verbosityLevelMap, $this->formatLevelMap);

        // utmdd($output->getVerbosity(), $this->verbosityLevelMap);

        // utmdd($output->getVerbosity(),$verbosityLevelMap);
        $this->dumper = new CliDumper;
        $this->cloner = new VarCloner;
        $this->output = $output;

        $filename = 'media_' . $this->channel;

        // if ('debug' == $level) {
        //     $filename = $filename.'_'.$level;
        // }

        $this->logfile = __LOGFILE_DIR__ . '/' . $filename . '.log';

        if (file_exists($this->logfile)) {
            $this->pruneLogFiles($this->logfile);
        }

        if (self::$USE_DEBUG === true) {
            $this->pruneLogFiles($this->debugLogFile(), 'debug');
        }

        self::$logger = $this;

        // $this->backtrace =
        // $i   = 1;
        // $tmp = debug_backtrace();
        // foreach ($tmp as $v) {
        //     $this->backtrace .= $i++.') '.$v['file'].'('.$v['line'].'): '.($v['class'] ?? '').($v['type'] ?? '').($v['function'] ?? '').\PHP_EOL;
        // }
    }

    public function pruneLogFiles($logfile, $level = 'info')
    {
        if (file_exists($logfile)) {
            if (self::$pruneLogs === true) {
                unlink($logfile);
            }
        }
        $msg = $this->interpolate(PHP_EOL . '===================================' . PHP_EOL . ' Running application {0}', [__SCRIPT_NAME__]);
        $this->file(
            $level,
            $msg
        );
    }

    private function debugLogFile()
    {
        return str_replace('.log', '_debug.log', $this->logfile);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        if (self::$USE_DEBUG == true) {
            $this->debugLog($message, $context);
        }

        $level  = LogLevel::DEBUG;
        $output = $this->output;
        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $output->writeln($this->ConsoleFormat($level, $message, $context));
        }

        if ($output->getVerbosity() >= $this->logVerbosityLevelMap[$level]) {
            $this->file($level, $this->LogFormat($level, $message, $context));
        }
    }

    public function debugLog($message, array $context = []): void
    {
        $level = 'debug';
        $this->logFormat($level, $message, $context);
        $this->file($level, $message);
    }

    private function LogFormat($level, $message, $context)
    {
        $string = Colors::colorstring($this->interpolate($message, $context), $this->ColorLevelMap[$level]);

        return sprintf('[%1$s]:%2$s', $level, $string);
    }

    private function ConsoleFormat($level, $message, $context)
    {
        $command = self::tracePath(true);

        return sprintf(
            '[%1$s]:[<file>%2$s</file>]<%3$s> %4$s</%3$s>',
            Colors::colorstring($level, $this->ColorLevelMap[$level]),
            $command,
            $this->formatLevelMap[$level],
            $this->interpolate($message, $context)
        );
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
        exit(1);
    }

    public function log($level, $message, array $context = []): void
    {
        if (! isset($this->verbosityLevelMap[$level])) {
            throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
        }

        $output = $this->output;

        // Write to the error output if necessary and available
        if ($this->formatLevelMap[$level] === self::ERROR) {
            if ($this->output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }
            $this->errored = true;
        }
        // the if condition check isn't necessary -- it's the same one that $output will do internally anyway.
        // We only do it for efficiency here as the message formatting is relatively expensive.

        // // utmdump([$level, $output->getVerbosity(), $this->verbosityLevelMap[$level]]);

        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $output->writeln($this->ConsoleFormat($level, $message, $context));
        }

        if ($output->getVerbosity() >= $this->logVerbosityLevelMap[$level]) {
            $this->file($level, $this->LogFormat($level, $message, $context));
        }
    }

    private function interpolate(string $message, array $context): string
    {
        if (! str_contains($message, '{')) {
            $class   = new PrettyArray;
            $context = $class->print($context);

            return $message . $context;
        }
        $replacements = [];

        foreach ($context as $key => $val) {
            if ($val === null || is_scalar($val) || $val instanceof Stringable) {
                $value = $val;
            } elseif ($val instanceof DateTimeInterface) {
                $value = $val->format(DateTimeInterface::RFC3339);
            } elseif (is_object($val)) {
                $value = trim($this->dumper->dump($this->cloner->cloneVar(get_class_vars($val::class)), true));
                $value = ' [object ' . $value . ']';
            } else {
                $value = ' ' . trim($this->dumper->dump($this->cloner->cloneVar($val), true));
            }
            $replacements["{{$key}}"] = Colors::colorstring($value, 'red');
        }

        return strtr($message, $replacements);
    }

    private function writeFile($level, $string) {}

    private function file($level, $message)
    {
        // $error = var_export($error,1);

        $error[] = trim('[' . date('Y-m-d H:i:s') . ']:' . $message);

        if ($level == 'debug') {
            $error[] = 'Trace:' . self::tracePath();
        }
        $error[] = '===================================';

        $text = implode("\n", $error);

        $logfile = $this->logfile;
        if ($level == 'debug') {
            $logfile = $this->debugLogFile();
        }

        file_put_contents($logfile, $text . PHP_EOL, FILE_APPEND);
    }
}
