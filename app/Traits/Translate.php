<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Closure;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;

use function is_array;

trait Translate
{
    public static $Class;

    public static function getClass()
    {
        return self::$Class;
    }

    public static function text($constant, $vars = [])
    {
        $class     = self::getClass();
        $thisClass = new ReflectionClass($class);
        $text      = $thisClass->getConstant($constant);

        if ($text == false) {
            if (! str_contains($class, 'Commands')) {
                $class = 'Locales\\Lang.php';
            }
            if (str_contains($class, 'Commands')) {
                $class = str_replace('Options', 'Lang.php', $class);
            }

            return '<error>' . $constant . ' not yet set in ' . $class . '</error> ';
        }

        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $key  = '%%' . strtoupper($key) . '%%';
                $text = str_replace($key, $value, $text);
            }

            $text = preg_replace_callback('|%%(\w+)%%|i', function ($matches) {
                return '';
            }, $text);
        }

        return $text;
    }
}
