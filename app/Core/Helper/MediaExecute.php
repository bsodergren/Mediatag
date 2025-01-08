<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Command\Command as SynCmd;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

trait MediaExecute
{
    public static $optionArg = [];

   

    public static function getProcessClass()
    {
        $className = static::class;

        // utmdump($className);

        if ($pos = strrpos($className, '\\')) {
            $class = substr($className, $pos + 1);
        }

        $tmpClass = str_replace("Command","",$class);
        // utmdump($tmpClass);

        $classPath = rtrim($className, $class);
        $classPath = str_replace($tmpClass,"",$classPath);
        $classPath = rtrim($classPath, 'Commands\\') . '\\';
        // utmdump($classPath);
        $classPath .= 'Process';

        // UTMlog::logger('Process Class', $classPath);
        // utmdd($classPath);
        return $classPath;
    }
}
