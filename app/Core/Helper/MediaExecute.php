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

        if ($pos = strrpos($className, '\\')) {
            $class = substr($className, $pos + 1);
        }
        $classPath = rtrim($className, $class);
        $classPath .= 'Process';

        // UTMlog::logger('Process Class', $classPath);
        return $classPath;
    }
}
