<?php
namespace Mediatag\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

class MediaLogger 
{
    private $backtrace;
    private $log;

    public function __construct(
        private LoggerInterface $logger,
    ) {

        $this->log = $logger;
        $i = 1;
		$tmp = debug_backtrace();
		foreach($tmp as $v) {
			$this->backtrace .= $i++.') '.$v['file'].'('.$v['line'].'): '.($v['class'] ?? '').($v['type'] ?? '').($v['function'] ?? '')."\r\n";
		}

    }

    public function doStuff(): void
    {
        $this->log->info('I love Tony Vairelles\' hairdresser.');
    }

    private function file($error)
	{
		$error = $error."\r\n".
			'--date: '.date("Y-m-d H:i:s")."\r\n".
			"Trace:\r\n".
			$this->backtrace.
			"\r\n===================================";

		file_put_contents('./logs/mysql.log',$error."\r\n\r\n",FILE_APPEND);

	}

	

	

	

	public function emergency($message, array $context = array())
	{
		return $this->alert($message, $context);
	}

	public function alert($message, array $context = array())
	{
		

		return $this->critical($message, $context);
	}

	public function critical($message, array $context = array())
	{
		return $this->error($message, $context);
	}

	public function error($message, array $context = array())
	{
			$this->file($message);
		


		return $this->warning($message, $context);
	}

	public function warning($message, array $context = array())
	{
		return $this->notice($message, $context);
	}

	public function notice($message, array $context = array())
	{
		return $this->info($message, $context);
	}

	public function info($message, array $context = array())
	{
		return $this->debug($message, $context);
	}

	public function debug($message, array $context = array())
	{
		
        $this->log->info($message);

		// return $this->log($message, $context);
	}

	public function log($level, $message, array $context = array())
	{
		return true;
	}


}


