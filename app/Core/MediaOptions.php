<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use const PHP_EOL;

use Mediatag\Core\Helper\OptionCompletion;
use Mediatag\Core\Helper\OptionsDefault;
use Mediatag\Core\Traits\ArgOptions;
use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Nette\Utils\Strings;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use function array_slice;
use function call_user_func;
use function count;
use function is_array;
use function is_object;
use function is_string;

/**
 * MediaOptions.
 */
class MediaOptions
{
    use ArgOptions;
    use Lang;

    // use OptionCompletion;
    use OptionsDefault;
    use Translate;

    public $options = ['Default' => true, 'Meta' => true, 'Test' => true, 'Display' => true];

    public static $callingClass;

    public static $CmdClass;

    public static $classObj;

    // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return null;
    // }

    // public function Definitions()
    // {
    //     // utminfo(func_get_args());

    //     return null;
    // }

    public static function getProcessClass()
    {
        $className = static::class;

        // utmdump($className);
        $pathInfo   = explode('\\', $className);
        $pathInfo   = array_slice($pathInfo, 0, 3);
        $pathInfo[] = 'Options';
        $className  = implode('\\', $pathInfo);

        return $className;
    }

    private static function getCommandOptions()
    {
        $className = self::$callingClass;
  // utmdump([__LINE__ => ['ClassName' => $className]]);
        if ($pos = strrpos($className, '\\')) {
            $class = substr($className, $pos + 1);
        }

        $tmpClass  = str_replace('Command', '', $class);
        $classPath = rtrim($className, $class);

        // $classPath = str_replace($tmpClass,"",$classPath);

        // $classPath = rtrim($classPath, 'Commands\\') . '\\';

        $classPath .= $tmpClass . 'Options';

  // utmdump([__LINE__ => ['classPath' => $classPath]]);

        if (class_exists($classPath)) {
            return $classPath;
        }

        $classPath = str_replace('Commands', 'Options', $classPath);
        // $OptionClassPath = Strings::after($className, 'Controller\\', 1);
        // // $OptionClassName = Strings::before($className, 'Commands\\', 1);

        // $CommandName = Strings::before($OptionClassPath, '\\', 1);
        // $className   = Strings::before($className, 'Commands\\', 1) . '\\Options\\';

        // $OptionClassName = Strings::before($className, $CommandName, 1);
        // utmdump(['classname' => $className, 'OptionClassPath' => $OptionClassPath, 'CommandName' => $CommandName, 'OptionClassName' => $OptionClassName]);
  // utmdump([__LINE__ => ['className' => $classPath]]);

        if (\class_exists($classPath)) {
            return $classPath;
        }
        self::$CmdClass = $className;

        return null;
    }

    public static function getClassObject($command)
    {
        // utminfo(func_get_args());

        $command = ucfirst(strtolower($command));
        // utmdump($command);
        $command = str_replace('Db', 'DB', $command);
        // $command = str_replace("Ph","PH",$command);

        // $className = $command.'\\Options';
        // $className = 'Mediatag\\Commands\\'.$className;

        $className = self::getCommandOptions();

        if ($className === null) {
            $className = self::$callingClass;

            // utmdump([$className, self::$CmdClass]);

            if ($pos = strrpos($className, '\\')) {
                $class = substr($className, $pos + 1);
            }

            $tmpClass = str_replace('Command', '', $class);

            $className = rtrim($className, $class);
            $className = str_replace($tmpClass, '', $className);
            // // utmdump($classPath);
            $className = rtrim($className, 'Commands\\') . '\\';
            $className .= 'Options';
        }

        if (class_exists($className)) {
            self::$classObj = new $className;
        }
    }

    /**
     * Method get.
     *
     * @param  mixed|null  $command
     */
    public static function getDefinition($command = null)
    {
        // utminfo(func_get_args());
        $testOptions    = [];
        $metaOptions    = [];
        $commandOptions = [];
        $definitions    = null;
        $cmdOptions     = [];
        self::getClassObject($command);
        if (is_object(self::$classObj)) {
            if (isset(self::$classObj->options)) {
                foreach (self::$classObj->options as $option => $value) {
                    if (is_string($option)) {
                        if ($value == false) {
                            continue;
                        }

                        $value = $option;
                    }
                    $cmd = 'get' . $value . 'Options';
                    if (method_exists(__CLASS__, $cmd)) {
                        $commandOptions[] = self::$cmd();
                    }
                }
            }
            // // utmdump(self::$classObj);
            $OptionMethod = $command . 'Options';
            if (method_exists(self::$classObj, $OptionMethod)) {
                $definitions = self::$classObj->$OptionMethod();
            } else {
                $definitions = self::$classObj->Definitions();
            }
            if (is_array($definitions)) {
                $cmdOptions = self::getOptions(
                    $definitions
                );
            }
        }

        foreach ($commandOptions as $Options) {
            $cmdOptions = array_merge($cmdOptions, $Options);
        }

        return new InputDefinition($cmdOptions);
    }

    public static function getOptions($optionArray)
    {
        // utminfo(func_get_args());

        if (! is_array($optionArray)) {
            return [];
        }

        $cnt            = count($optionArray);
        $commandOptions = [];
        $i              = 0;
        $prev           = '';

        foreach ($optionArray as $idx => $optionName) {
            $i++;
            $breakText = '';
            if ($optionName[0] == 'break') {
                $key = $idx - 1;
                $prev[3] .= PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL; // .str_pad('',__CONSOLE_WIDTH__ - 50,"-").PHP_EOL;
                $commandOptions[$key] = new InputOption(...$prev);

                continue;
            }

            if ($i == $cnt) {
                $optionName[3] .= PHP_EOL;
            }
            $prev = $optionName;

            $name        = null;
            $shortcut    = null;
            $mode        = null;
            $description = null;
            $default     = null;
            foreach ($optionName as $id => $v) {
                switch ($id) {
                    case 0:
                        $name = $optionName[$id];
                        break;
                    case 1:
                        $shortcut = $optionName[$id];
                        break;
                    case 2:
                        $mode = $optionName[$id];
                        break;
                    case 3:
                        $description = $optionName[$id];
                        break;
                    case 4:
                        $default = $optionName[$id];
                        break;
                }
            }

            if ($mode != 1) {
                $commandOptions[] = new InputOption(
                    $name,
                    $shortcut,
                    $mode,
                    $description,
                    $default,
                    function (CompletionInput $input) use ($name) {
                        if (method_exists(self::$classObj, 'optionClosure')) {
                            // utmdump(['exsts', $name, method_exists(self::$classObj, 'optionClosure')]);

                            return call_user_func([self::$classObj, 'optionClosure'], $input, $name);
                        } else {
                            // utmdump(['no no exsts', $name]);

                            return $this->optionClosure($input, $name);
                        }
                    }
                );
            } else {
                $commandOptions[] = new InputOption($name, $shortcut, $mode, $description, $default);
            }
        }

        return $commandOptions;
    }

    public function optionClosure($input, $option)
    {
        $returnValue = null;
        $cmd         = 'list' . ucfirst($option);

        // utmdump([$cmd, method_exists($this, $cmd)]);
        $currentValue = $input->getCompletionValue();
        if (method_exists($this, $cmd)) {
            $returnValue = $this->$cmd($currentValue);
        }

        return $returnValue;
    }
}
