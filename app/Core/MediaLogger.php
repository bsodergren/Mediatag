<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Stringable;
use Psr\Log\LogLevel;
use UTM\Utilities\Colors;
use UTM\Utilities\Option;
use Psr\Log\LoggerInterface;
use UTM\Utilities\Debug\Debug;
use Mediatag\Core\Helper\LogFormat;
use UTM\Utilities\Debug\PrettyArray;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class MediaLogger extends ConsoleLogger implements LoggerInterface
{
    use LogFormat;

    public const INFO = 'info';
    public const ERROR = 'error';
    public const NOTICE = 'playlist';

    private $backtrace = '';
    // private $log;
    public static $logger;
    public static $USE_DEBUG = false;
    public static $pruneLogs = false;
    private bool $errored    = false;
private $colors;
    private $output;
    private $dumper;
    private $cloner;
    private $channel = 'default';
    private $logfile;
    // private $logfile;
      private array $verbosityLevelMap = [
        LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::INFO => OutputInterface::VERBOSITY_VERY_VERBOSE,
        LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
    ];
    private array $logVerbosityLevelMap = [
        // LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::INFO   => OutputInterface::VERBOSITY_VERY_VERBOSE,
        // LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::ALERT     => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::CRITICAL  => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::ERROR     => OutputInterface::VERBOSITY_NORMAL,
        // LogLevel::WARNING   => OutputInterface::VERBOSITY_NORMAL,
        LogLevel::NOTICE    => OutputInterface::VERBOSITY_VERBOSE,
        LogLevel::INFO      => OutputInterface::VERBOSITY_QUIET,
        // LogLevel::DEBUG     => OutputInterface::VERBOSITY_DEBUG,
    ];



	private array $ColorLevelMap = [
        LogLevel::DEBUG     => "cyan",
		LogLevel::INFO     => "green",
        LogLevel::NOTICE    => "blue",
        LogLevel::ERROR     => 'red',
        LogLevel::CRITICAL  => 'orange',

    ];

	private array $formatLevelMap = [
        LogLevel::EMERGENCY => self::ERROR,
        LogLevel::ALERT     => self::ERROR,
        LogLevel::CRITICAL  => self::ERROR,
        LogLevel::ERROR     => self::ERROR,
        LogLevel::WARNING   => self::INFO,
        LogLevel::NOTICE    => self::NOTICE,
        LogLevel::INFO      => self::INFO,
        LogLevel::DEBUG     => self::INFO,
    ];

    public function __construct($output, $channel = 'default')
    {
        $this->channel = $channel;

		$this->colors = new Colors();
        // $this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
        // $this->formatLevelMap    = $formatLevelMap    + $this->formatLevelMap;

        // utmdump([$this->verbosityLevelMap, $this->formatLevelMap]);
        parent::__construct($output  , $this->verbosityLevelMap, $this->formatLevelMap  );
//        $log = new ConsoleLogger($output);//, $this->verbosityLevelMap, $this->formatLevelMap);

// utmdd($this->verbosityLevelMap);
        $this->dumper = new CliDumper();
        $this->cloner = new VarCloner();
        $this->output = $output;

        $filename = 'media_'.$this->channel;

        // if ('debug' == $level) {
        //     $filename = $filename.'_'.$level;
        // }

        $this->logfile = __LOGFILE_DIR__.'/'.$filename.'.log';

        if (file_exists($this->logfile)) {
            $this->pruneLogFiles($this->logfile);
        }

        if (true === self::$USE_DEBUG) {
            $this->pruneLogFiles($this->debugLogFile(),'debug');
        }

        self::$logger = $this;

        // $this->backtrace =
        // $i   = 1;
        // $tmp = debug_backtrace();
        // foreach ($tmp as $v) {
        //     $this->backtrace .= $i++.') '.$v['file'].'('.$v['line'].'): '.($v['class'] ?? '').($v['type'] ?? '').($v['function'] ?? '').\PHP_EOL;
        // }
    }

    public function pruneLogFiles($logfile,$level="info")
    {
        if (file_exists($logfile)) {
            if (true === self::$pruneLogs) {
                unlink($logfile);
            }
        }
		$msg = $this->interpolate(PHP_EOL.'==================================='.PHP_EOL.' Running application {0}',  [__SCRIPT_NAME__]);
        $this->file(
			$level,
            $msg);
    }

    private function debugLogFile()
    {
        return str_replace('.log', '_debug.log', $this->logfile);
    }
    // public function alert(string|\Stringable $message, array $context = []): void
    // {
    // }

    // public function critical(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("error",$message,$context);
    // }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        if (true == self::$USE_DEBUG) {
            $this->debugLog($message, $context);
        }
    }

    // public function emergency(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("error",$message,$context);
    // }

    // public function error(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("error",$message,$context);
    // }

    // public function info(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("info",$message,$context);
    // }

    // public function notice(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("notice",$message,$context);
    // }

    // public function warning(string|\Stringable $message, array $context = []): void
    // {
    // 	$this->log("warning",$message,$context);
    // }
    public function debugLog($message, array $context = []): void
    {

		$level = 'debug';

		$this->logFormat($level,$message,$context);
		
        $this->file($level, $message);

    }

	private function LogFormat($level,$message,$context){


		$string = Colors::colorstring($this->interpolate($message, $context),$this->ColorLevelMap[$level]);

		return \sprintf('[%1$s]:%2$s', $level, $string);

	}

	private function ConsoleFormat($level,$message,$context){
        $command = self::tracePath(true);

        return \sprintf('[%1$s]:[<file>%2$s</file>]<%3$s> %4$s</%3$s>',
        $level,
        $command,
         $this->formatLevelMap[$level],        
           $this->interpolate($message, $context));

	}


    public function log($level, $message, array $context = []): void
    {
        if (!isset($this->verbosityLevelMap[$level])) {
            throw new InvalidArgumentException(\sprintf('The log level "%s" does not exist.', $level));
        }

        $output = $this->output;

        // Write to the error output if necessary and available
        if (self::ERROR === $this->formatLevelMap[$level]) {
            if ($this->output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }
            $this->errored = true;
        }
        // the if condition check isn't necessary -- it's the same one that $output will do internally anyway.
        // We only do it for efficiency here as the message formatting is relatively expensive.

        // utmdump([$level, $output->getVerbosity(), $this->verbosityLevelMap[$level]]);

        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $output->writeln($this->ConsoleFormat($level,$message,$context));
        }

        
        if ($output->getVerbosity() >= $this->logVerbosityLevelMap[$level]) {
            $this->file($level, $this->LogFormat($level,$message,$context));
        }
    }

    private function interpolate(string $message, array $context): string
    {
        if (!str_contains($message, '{')) {
            $class   = new PrettyArray();
            $context = $class->print($context);

            return $message.$context;
        }
        $replacements = [];
        foreach ($context as $key => $val) {
            if (null === $val || \is_scalar($val) || $val instanceof \Stringable) {
                $value = $val;
            } elseif ($val instanceof \DateTimeInterface) {
                $value = $val->format(\DateTimeInterface::RFC3339);
            } elseif (\is_object($val)) {
                $value = trim($this->dumper->dump($this->cloner->cloneVar(get_class_vars($val::class)), true));
                $value = ' [object '.$value.']';
            } else {
                $value = ' '.trim($this->dumper->dump($this->cloner->cloneVar($val), true));
            }
            $replacements["{{$key}}"] = $value;
        }

        return strtr($message, $replacements);
    }

    private function writeFile($level, $string)
    {
    }

    private function file($level, $message)
    {
        // $error = var_export($error,1);

        $error[] = trim('['.date('Y-m-d H:i:s').']:'.$message);

        if ('debug' == $level) {
            $error[] = 'Trace:'.self::tracePath();
        }
        $error[] = '===================================';

        $text = implode("\n", $error);

        $logfile = $this->logfile;
        if ('debug' == $level) {
            $logfile = $this->debugLogFile();
        }

        file_put_contents($logfile, $text.\PHP_EOL, \FILE_APPEND);
    }

    // public function __call($method, $args)
    // {
    //     return Mediatag::$log->$method(...$args);
    // }

    // public function emergency(string|Stringable $message, array $context = [])
    // {
    //     return $this->alert($message, $context);
    // }

    // public function alert($message, array $context = [])
    // {
    //     return $this->critical($message, $context);
    // }

    // public function critical($message, array $context = [])
    // {
    //     return $this->error($message, $context);
    // }

    // public function error($message, array $context = [])
    // {
    //     $this->file($message);

    //     return $this->warning($message, $context);
    // }

    // public function warning($message, array $context = [])
    // {
    //     return $this->notice($message, $context);
    // }

    // public function notice($message, array $context = [])
    // {
    //     return $this->info($message, $context);
    // }

    // public function info($message, array $context = [])
    // {
    //     return $this->debug($message, $context);
    // }

    // public function debug($message, array $context = [])
    // {
    //     $this->log->info($message);

    //     // return $this->log($message, $context);
    // }

    // public function log($level, $message, array $context = [])
    // {
    //     return true;
    // }
}
