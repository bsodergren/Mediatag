<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Symfony\Component\Filesystem\Filesystem;

use function array_slice;

trait CommandHelper
{
    private $defaultValues = [];

    private $completionCmd = [];

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
