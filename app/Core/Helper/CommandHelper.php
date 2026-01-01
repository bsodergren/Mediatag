<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use function array_slice;

use Mediatag\Core\Mediatag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

trait CommandHelper
{
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
            if (!is_dir($dir)) {
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
        if (!class_exists($class)) {
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

    public function setDefault($command, $default)
    {
        $this->defaultValues[$command] = $default;
    }

    public function setCompletionCmd($command, $value)
    {
        $this->completionCmd[$command] = $value;
    }
}
