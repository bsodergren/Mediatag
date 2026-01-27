<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Mediatag\Utilities\Strings;
use ReflectionClass;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

use function array_slice;

trait CommandHelper
{
    public $compCommand = [];

    private $defaultValues = [];

    private $completionCmd = [];

    // protected function loadStyles($input, $output)
    // {

    //     // $output    = $output;
    //     $this->io        = new SymfonyStyle($input, $output);
    //     $this->formatter = new FormatterHelper;

    //     Mediatag::$output->getFormatter()->setStyle('indent', new OutputFormatterStyle('red'));
    //     Mediatag::$output->getFormatter()->setStyle('current', new OutputFormatterStyle('magenta'));
    //     Mediatag::$output->getFormatter()->setStyle('update', new OutputFormatterStyle('bright-green'));

    //     Mediatag::$output->getFormatter()->setStyle('id', new OutputFormatterStyle('yellow'));
    //     Mediatag::$output->getFormatter()->setStyle('text', new OutputFormatterStyle('green'));
    //     Mediatag::$output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
    //     Mediatag::$output->getFormatter()->setStyle('playlist', new OutputFormatterStyle('bright-magenta'));
    //     Mediatag::$output->getFormatter()->setStyle('download', new OutputFormatterStyle('blue'));
    //     Mediatag::$output->getFormatter()->setStyle('file', new OutputFormatterStyle('bright-cyan'));
    //     // Mediatag::$output = $output;

    //     $styleArray = Mediatag::$output->getFormatter()->getStyles();

    //     utmdump(Mediatag::$output instanceof ConsoleOutputInterface);
    //     if (Mediatag::$output instanceof ConsoleOutputInterface) {
    //         Mediatag::$output = $output->getErrorOutput();
    //         foreach ($styleArray as $name => $obj) {
    //             Mediatag::$output->getFormatter()->setStyle($name, $obj);
    //         }
    //     }

    //     Mediatag::$input = $input;
    //     //Mediatag::$output = $noutput;
    //     Mediatag::$Io = $this->io;

    // }

    protected function loadDirs()
    {
        $filesystem = new Filesystem;
        foreach (__CREATE_DIRS__ as $dir) {
            if (! is_dir($dir)) {
                $filesystem->mkdir($dir);
            }
        }
    }

    public static function getProcessClass($className = null)
    {
        if (is_null($className)) {
            $className = static::class;
        }

        $class = preg_replace('/([a-zA-Z\]+.*)\\([a-zA-Z]+)?(Command)$/', '$1Process', $className);
        // preg_match('/([a-zA-Z\]+.*)\\([a-zA-Z]+)?(Command)/', $className, $output_array);
        if (! class_exists($class)) {
            $pathInfo   = explode('\\', $class);
            $pathInfo   = array_slice($pathInfo, 0, 3);
            $pathInfo[] = 'Process';
            $class      = implode('\\', $pathInfo);
        }

        return $class;
    }

    public static function ArgumentClosure($input, $command)
    {
        // utmdump(['CommandHelper', $command]);
        // the value the user already typed, e.g. when typing "app:greet Fa" before
        // pressing Tab, this will contain "Fa"
        $currentValue = $input->getCompletionValue();

        return $currentValue;
    }

    public static function OptionClosure($input, $command)
    {
        // utmdump(['CommandHelper', $command]);
        // the value the user already typed, e.g. when typing "app:greet Fa" before
        // pressing Tab, this will contain "Fa"
        $currentValue = $input->getCompletionValue();

        return $currentValue;
    }

    // public function setDefault($command, $default)
    // {
    //     $this->defaultValues[$command] = $default;
    // }

    // public function setCompletionCmd($command, $value)
    // {
    //     $this->completionCmd[$command] = $value;
    // }

    public static function findClass($class)
    {
        foreach (get_declared_classes() as $classList) {
            if (str_ends_with($classList, $class)) {
                return $classList;
            }
        }

        return $class;
    }

    public static function call_user_class($command, $parse = true)
    {
        if (is_array($command)) {
            [$cmdClass, $cmdMethod] = $command;
        } elseif (str_contains($command, '::')) {
            [$cmdClass, $cmdMethod] = explode('::', $command);
        } else {
            return $command;
        }

        $cmdClass = self::findClass($cmdClass);

        $command = [$cmdClass, $cmdMethod];

        if (! method_exists($cmdClass, $cmdMethod)) {
            //            $command = (new ReflectionClass($cmdClass))->getConstant($cmdMethod);

            $command = $cmdClass . '::' . $cmdMethod;

            $parse = false;
        }

        // utmdump($command);

        if ($parse === true) {
            return call_user_func($command);
        }

        return $command;
    }

    // private function processHandler($Helper, $commandName, $targetName, $type, $completion = null)
    // {
    //     // $Helper = '';

    //     if ($Helper != 'Completion') {
    //         $Helper = 'Completion\\' . $Helper;
    //     }

    //     $Class = 'Mediatag\\Bundle\\BashCompletion\\' . $Helper;

    //     $type        = self::call_user_class($type, true);
    //     $commandName = self::call_user_class($commandName, true);

    //     $args = [$commandName, $targetName, $type];

    //     if ($completion !== null) {
    //         $completion = self::call_user_class($completion);

    //         $args[] = $completion;
    //     }
    //     // utmdump($args);
    //     self::$CompletionHandlers[] = new $Class(...$args);
    // }

    // public function setCompletionHandler()
    // {
    //     $commandKeys = array_keys($this->command);
    //     $Handlers    = [];

    //     foreach ($commandKeys as $i => $command) {
    //         $Handlers[$command] = [];

    //         foreach ($this->Handlers['handler'] as $handlerRow) {
    //             $Handlers[$command][] = $handlerRow;
    //         }

    //         $handler = array_key_first($this->command[$command]);

    //         if ($handler != 'handler') {
    //             continue;
    //         }
    //         $Handlers[$command][] = $this->command[$command][$handler];
    //     }

    //     // $commandName, $targetName, $type, $completion)

    //     foreach ($Handlers as $command => $HandlerRow) {
    //         foreach ($HandlerRow as $HandlerArray) {
    //             $HelperName  = isset($HandlerArray['Helper']) ? $HandlerArray['Helper'] : null;
    //             $commandName = isset($HandlerArray['commandName']) ? $HandlerArray['commandName'] : $command;

    //             $targetName = isset($HandlerArray['targetName']) ? $HandlerArray['targetName'] : null;
    //             $type       = isset($HandlerArray['type']) ? $HandlerArray['type'] : null;
    //             $completion = isset($HandlerArray['completion']) ? $HandlerArray['completion'] : null;
    //             if ($HelperName === null || $type === null || $targetName === null) {
    //                 continue;
    //             }
    //             // utmdump([$HelperName, $commandName, $targetName, $type]);
    //             $this->processHandler($HelperName, $commandName, $targetName, $type, $completion);
    //         }
    //     }

    //     // utmdd('');

    //     return self::$CompletionHandlers;
    // }

    // public static function getCompletionHandler()
    // {
    //     foreach (self::$CompletionHandlers as $handler) {
    //         // utmdump($handler);
    //     }
    // }
}
