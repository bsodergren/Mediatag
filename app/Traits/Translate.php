<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Closure;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputArgument;

trait Translate
{
    public static $Class;

    public static function getClass()
    {
        return Translate::$Class;
    }
    public static function text($constant, $vars = [])
    {

        $class     = self::getClass();
        $thisClass = new \ReflectionClass($class);
        $text      = $thisClass->getConstant($constant);

        if (false == $text) {
            if (!str_contains($class, 'Commands')) {
                $class = 'Locales\\Lang.php';
            }
            if (str_contains($class, 'Commands')) {
                $class = str_replace('Options', 'Lang.php', $class);
            }

            return '<error>' . $constant . ' not yet set in ' . $class . '</error> ';
        }

        if (\is_array($vars)) {
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

    public function Arguments(string $name, string $description = '', ?int $mode = InputArgument::OPTIONAL, mixed $default = null, array|\Closure $suggestedValues = [])

    // public function Arguments($varName = null, $description = null, $inputArgs =InputArgument::OPTIONAL, $defaultValue = null, $CompletionInput = Closure)
    {
        // utminfo(func_get_args());

        return [$name, $mode, $description,$default,$suggestedValues ];
    }
}
