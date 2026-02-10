<?php

namespace Mediatag\Core\Traits\Command;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\MediatagExec;
use Nette\Utils\Callback;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

trait CommandExecute
{
    public static $optionArg = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $this->loadStyles($input, $output);
        $cmdArgument             = null;
        $originalCommand         = null;
        Mediatag::$ProcessHelper = $this->getHelper('process');

        if (Option::istrue('trunc')) {
            Mediatag::$dbconn->truncate();

            return Command::SUCCESS;
        }

        $class     = static::class;
        $arguments = $input->getArguments();
        utmdump($arguments);
        if (count($arguments) > 0) {
            $cmdArgument = $input->getArgument($this->getName());

            if (! is_null($cmdArgument)) {
                if (array_key_exists($arguments['command'], $arguments)) {
                    if ($cmdArgument == $arguments[$arguments['command']]) {
                        $cmdArgument     = null;
                        $originalCommand = $this->getName();
                    }
                }
                // utmdd($cmdArgument);
            }

            if ($cmdArgument !== null) {
                self::$optionArg = array_merge(self::$optionArg, [$cmdArgument]);
            }
        }

        $class = self::getProcessClass();
        // utmdd(self::$optionArg);
        $Process = new $class($input, $output, self::$optionArg);
        // $this->Handlers = $Process->Handlers;

        // $Process->completionHandlers = $this->setCompletionHandler();
        // utmdd($arguments, $this->command);
        $Process->commandList = array_merge($Process->commandList, $this->command);
        $method               = 'process';
        if (array_key_exists('command', $arguments)) {
            $method = $arguments['command'];
        }
        utmdump($method);
        $Process->$method();

        if ($originalCommand !== null) {
            $args = [__SCRIPT_NAME__, $arguments[$arguments['command']]];
            $exec = new MediatagExec(null, $input, $output);
            $exec->exec($args, Callback::check([$exec, 'Output']), true);
        }
        // if (!is_null($arguments[$arguments['command']])) {
        //     $class = str_ireplace(ucfirst($arguments['command']), ucfirst($arguments[$arguments['command']]), static::class);
        //     utmdump($class);
        //     $Process2 = new $class($input, $output, self::$optionArg);
        //     utmdump($class, $Process2);
        // }

        return Command::SUCCESS;
    }
}
