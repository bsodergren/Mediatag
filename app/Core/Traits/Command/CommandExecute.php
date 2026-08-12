<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Traits\Command;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Executable\MediatagExec;
use Nette\Utils\Callback;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;

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
            Storage::$DB->truncate();

            return Command::SUCCESS;
        }

        $class     = static::class;
        $arguments = $input->getArguments();
        // utmdd($arguments);
        if (count($arguments) > 0) {
            $cmdArgument = $input->getArgument($this->getName());

            if (null !== $cmdArgument) {
                if (array_key_exists($arguments['command'], $arguments)) {
                    if ($cmdArgument == $arguments[$arguments['command']]) {
                        $cmdArgument     = null;
                        $originalCommand = $this->getName();
                    }
                }
                // utmdd($cmdArgument);
            }

            if (null !== $cmdArgument) {
                self::$optionArg = array_merge(self::$optionArg, [$cmdArgument]);
            }
        }

        $class = self::getProcessClass();
        // utmdd(self::$optionArg);
        $Process = new $class($input, $output, self::$optionArg);

        // $this->Handlers = $Process->Handlers;

        // $Process->completionHandlers = $this->setCompletionHandler();
        $Process->commandList = array_merge($Process->commandList, $this->command);
        $method               = 'process';
        // utmdd( );

        if (array_key_exists('command', $arguments)) {
            $method = $arguments['command'];
        }
        $Process->$method();

        if (null !== $originalCommand) {
            $args = [__SCRIPT_NAME__, $arguments[$arguments['command']]];

            // utmdump([array_key_exists($arguments[$arguments['command']], $Process->commandList),
            //  $arguments[$arguments['command']],
            //  $Process->commandList]);
            if (!array_key_exists($arguments[$arguments['command']], $Process->commandList)) {
                return Command::SUCCESS;
            }

            $method = $arguments['command'];

            $exec = new MediatagExec(null, $input, $output);
            $exec->exec($args, Callback::check([$exec, 'Output']), true);
        }
        // if (!is_null($arguments[$arguments['command']])) {
        //     $class = str_ireplace(ucfirst($arguments['command']), ucfirst($arguments[$arguments['command']]), static::class);
        //     // utmdump($class);
        //     $Process2 = new $class($input, $output, self::$optionArg);
        //     // utmdump($class, $Process2);
        // }

        return Command::SUCCESS;
    }
}
