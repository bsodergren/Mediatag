<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Locales\Lang;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * MediaOptions.
 */
class MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['Default' => false, 'Meta' => false, 'Test' => false, 'Display' => false];

    public static $callingClass;

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
        $pathInfo   = \array_slice($pathInfo, 0, 3);
        $pathInfo[] = 'Options';
        $className  = implode('\\', $pathInfo);

        return $className;
    }

    private static function getCommandOptions()
    {
        $className = self::$callingClass;

        if ($pos = strrpos($className, '\\')) {
            $class = substr($className, $pos + 1);
        }

        $tmpClass = str_replace('Command', '', $class);

        $classPath = rtrim($className, $class);

        // $classPath = str_replace($tmpClass,"",$classPath);
        // utmdump($classPath);

        // $classPath = rtrim($classPath, 'Commands\\') . '\\';
        // utmdump($classPath);

        $classPath .= $tmpClass.'Options';
        if (class_exists($classPath)) {
            return $classPath;
        }

        return null;
    }

    public static function getClassObject($command)
    {
        // utminfo(func_get_args());
        $command = ucfirst(strtolower($command));
        $command = str_replace('Db', 'DB', $command);
        // $command = str_replace("Ph","PH",$command);

        // $className = $command.'\\Options';
        // $className = 'Mediatag\\Commands\\'.$className;

        $className = self::getCommandOptions();
        if (null === $className) {
            $className = self::$callingClass;

            if ($pos = strrpos($className, '\\')) {
                $class = substr($className, $pos + 1);
            }

            $tmpClass = str_replace('Command', '', $class);

            $className = rtrim($className, $class);
            $className = str_replace($tmpClass, '', $className);
            // utmdump($classPath);
            $className = rtrim($className, 'Commands\\').'\\';
            $className .= 'Options';
        }

        if (class_exists($className)) {
            self::$classObj = new $className();
        }
    }

    /**
     * Method get.
     *
     * @param mixed|null $command
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
        if (\is_object(self::$classObj)) {
            if (isset(self::$classObj->options)) {
                foreach (self::$classObj->options as $option => $value) {
                    if (\is_string($option)) {
                        if (false == $value) {
                            continue;
                        }

                        $value = $option;
                    }
                    $cmd = 'get'.$value.'Options';
                    if (method_exists(__CLASS__, $cmd)) {
                        $commandOptions[] = self::$cmd();
                    }
                }
            }

            $definitions = self::$classObj->Definitions();
            if (\is_array($definitions)) {
                $cmdOptions = self::getOptions($definitions);
            }
        }

        foreach ($commandOptions as $Options) {
            $cmdOptions = array_merge($cmdOptions, $Options);
        }
        // utmdump($cmdOptions);

        return new InputDefinition($cmdOptions);
    }

    public static function getArguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        //    self::getClassObject();
        if (\is_object(self::$classObj)) {
            return self::$classObj->Arguments($varName, $description);
        }

        return null;
    }

    public static function getOptions($optionArray)
    {
        // utminfo(func_get_args());

        if (!\is_array($optionArray)) {
            return [];
        }

        $cnt            = \count($optionArray);
        $commandOptions = [];
        $i              = 0;
        $prev           = '';

        foreach ($optionArray as $idx => $optionName) {
            ++$i;
            $breakText = '';
            if ('break' == $optionName[0]) {
                $key = $idx - 1;
                $prev[3] .= \PHP_EOL.\PHP_EOL; // .str_pad('',__CONSOLE_WIDTH__ - 50,"-").PHP_EOL;
                $commandOptions[$key] = new InputOption(...$prev);

                continue;
            }

            if ($i == $cnt) {
                $optionName[3] .= \PHP_EOL;
            }
            $prev             = $optionName;
            $commandOptions[] = new InputOption(...$optionName);
        }

        return $commandOptions;
    }

    public static function getDefaultOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['filelist', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DEFAULT_FILELIST')],
            ['numberofFiles', 'N', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_NUMBEROFFILES')],
            ['max', 'M', InputOption::VALUE_REQUIRED, Translate::text('L__DEFAULT_MAX')],
            ['range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DEFAULT_RANGE')],
            ['filenumber', 'F', InputOption::VALUE_REQUIRED, Translate::text('L__DEFAULT_FILENUMBER')],
            ['new', '', InputOption::VALUE_NONE, Translate::text('L__DEFAULT_SHOW_NEWFILES')],
        ];

        return self::getOptions($options);
    }

    public static function getQuestionOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['ask', null, InputOption::VALUE_NEGATABLE, Translate::text('L__DEFAULT_ASK_FILE')],
            ['overwrite', 'o', InputOption::VALUE_NEGATABLE, Translate::text('L__DEFAULT_OVERWRITE_FILE')],
            ['yes', 'y', InputOption::VALUE_NEGATABLE, Translate::text('L__DEFAULT_QUESTION_YES')],
        ];

        return self::getOptions($options);
    }

    public static function getTestOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        $options = [
            ['test', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_CMD')],
            ['preview', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_PREVIEW')],
            ['time', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_TIME')],
            ['dump', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_DUMP')],
            ['flush', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_FLUSH')],
            ['nocache', null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_FLUSH')],
            // ['trunc',null, InputOption::VALUE_NONE, Translate::text('L__DEFAULT_TEST_TRUNC')],
        ];

        return self::getOptions($options);
    }

    public static function getMetaOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;
        $cmdName          = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options          = [
            ['only', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_ONLY', ['TXT' => $cmdName]), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['title', 't', InputOption::VALUE_REQUIRED, Translate::text('L__META_TITLE', ['TXT' => $cmdName])],
            ['genre', 'g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_GENRE', ['TXT' => $cmdName])],
            ['studio', 's', InputOption::VALUE_REQUIRED, Translate::text('L__META_STUDIO', ['TXT' => $cmdName])],
            ['network', 'n', InputOption::VALUE_REQUIRED, Translate::text('L__META_NETWORK', ['TXT' => $cmdName])],

            ['artist', 'a', InputOption::VALUE_REQUIRED, Translate::text('L__META_ARTIST', ['TXT' => $cmdName])],
            ['keyword', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__META_KEYWORD', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }

    public static function getDisplayOptions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;
        $cmdName          = ucfirst(str_replace('media', '', __SCRIPT_NAME__));
        $options          = [
            ['show', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_SHOW', ['TXT' => $cmdName])],
            ['hide', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_HIDE', ['TXT' => $cmdName])],
            ['add', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_ADD', ['TXT' => $cmdName])],
            ['drop', '', InputOption::VALUE_NONE, Translate::text('L__DISPLAY_DROP', ['TXT' => $cmdName])],
        ];

        return self::getOptions($options);
    }
}
