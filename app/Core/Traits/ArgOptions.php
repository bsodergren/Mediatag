<?php

namespace Mediatag\Core\Traits;

use Closure;
use Symfony\Component\Console\Input\InputArgument;

trait ArgOptions
{
    public function Arguments(string $name, string $description = '', ?int $mode = InputArgument::OPTIONAL, mixed $default = null, array|Closure $suggestedValues = [])

    // public function Arguments($varName = null, $description = null, $inputArgs =InputArgument::OPTIONAL, $defaultValue = null, $CompletionInput = Closure)
    {
        // utminfo(func_get_args());

        return [$name, $mode, $description, $default, $suggestedValues];
    }

    public static function getArgument($command = null)
    {
        // $definitions = [];

        self::getClassObject($command);
        if (is_object(self::$classObj)) {
            $definitions = self::$classObj->CmdArguments();
            if (is_array($definitions)) {
                return $definitions;
            }
        }

        return null;
    }

    public static function getArguments($varName, $description, $closure)
    {
        // utminfo(func_get_args());

        //    self::getClassObject();

        if (is_object(self::$classObj)) {
            return self::$classObj->Arguments($varName, $description, InputArgument::OPTIONAL, null, $closure);
        }

        return null;
    }

    public function CmdArguments()
    {
        // utminfo(func_get_args());
        return null;
    }
}
