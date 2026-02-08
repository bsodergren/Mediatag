<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Commands\Create\ClassMethods;
use Mediatag\Commands\Create\Traits\CreateBaseCommandHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Utilities\Chooser;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

use function is_array;

trait Helper
{
    use ClassMethods;
    use CreateBaseCommandHelper;

    public $excludeFiles = [];

    private $functions = [
        'Command' => [
            'getClassBase',
            'setNameSpace',
            'getUseClasses',
            'getCommandHeader',
            'addConstants',
            'addDefaultCommand',
            'getTraits',
        ],
        'Helper'  => [
            'getClassBase',
            'setNameSpace',
            'getUseClasses',
            'addMethods',
            'getTraits',
        ],
        'Process' => [
            'getClassBase',
            'setNameSpace',
            'getUseClasses',
            'getTraits',
        ],
        'Options' => [
            'getClassBase',
            'setNameSpace',
            'getUseClasses',
            'getTraits',
            'addOptionBody',

        ],

    ];

    public function createCommand()
    {
        $this->excludeFiles = Option::getValue('exclude', true, []);
        if (! is_array($this->excludeFiles)) {
            $tmp[] = $this->excludeFiles;
            unset($this->excludeFiles);
            $this->excludeFiles = $tmp;
        }
        if (count($this->excludeFiles) > 0) {
            $this->excludeFiles = array_map('ucfirst', $this->excludeFiles);
        }

        $type = Option::getValue('type', true);
        if ($type) {
            $type = ucfirst($type);
        }
        $userCommand = Option::getValue('userCommand', true);
        if ($userCommand !== null) {
            $parts = explode(':', $userCommand);
            if (count($parts) === 3) {
                $type = ucfirst($parts[2]);
            }
        }
        foreach ($this->functions as $fileType => $methods) {
            if (count($this->excludeFiles) > 0) {
                if (in_array($fileType, $this->excludeFiles)) {
                    Mediatag::$Console->writeln('skipping ' . $fileType);

                    continue;
                }
            }
            if (ucfirst($type) != ucfirst($fileType)) {
                continue;
            }
            $this->parseOptions($fileType);
            foreach ($methods as $method) {
                $this->$method();
            }
            $this->saveClass();
        }
    }

    // public function langCommand()
    // {
    //     $translationKey  = Option::getValue('name', true);
    //     $CommandFileName = Option::getValue('cmd', true);
    //     $desc            = Option::getValue('desc', true);

    //     $string = "\t" . 'public const ' . $translationKey . '            = \'' . $desc . '\';';

    //     $MediaCommand = ucfirst(strtolower(Option::getValue('cmd', true)));

    //     if ($MediaCommand == 'Global') {
    //         $this->LANGUAGE_FILE = $this->GLOBAL_LANG;
    //     } else {
    //         $this->LANGUAGE_FILE = $this->parse($this->LANGUAGE_FILE, ['CMD_NAME' => ucfirst($MediaCommand)]);
    //     }

    //     $lines = FileSystem::readLines($this->LANGUAGE_FILE);
    //     foreach ($lines as $lineNum => $line) {
    //         if (str_contains($line, $translationKey)) {
    //             Mediatag::$output->writeln('already tehre');

    //             return false;
    //         }
    //         if ($line == '}') {
    //             $newFile[] = $string;
    //             $newFile[] = '}';

    //             continue;
    //         }
    //         $newFile[] = $line;
    //     }
    //     $contents = implode("\n", $newFile);
    //     // utmdd($contents);
    //     FileSystem::write($this->LANGUAGE_FILE, $contents);

    //     utmdd($this->LANGUAGE_FILE);
    // }

    //     $params          = [];
    //     $CommandName     = Option::getValue('name', true);
    //     $CommandFileName = ucfirst($CommandName . 'Command');
    //     $desc            = Option::getValue('desc', true);
    //     if ($desc === null) {
    //         $desc = $CommandName . ' command';
    //     }

    //     $params['USELIBRARY'] = 'true';
    //     $params['SKIPSEARCH'] = 'false';

    //     if (Option::isTrue('Library') === false) {
    //         $params['USELIBRARY'] = 'false';
    //     }
    //     if (Option::isTrue('Search') === false) {
    //         $params['SKIPSEARCH'] = 'true';
    //     }

    //     $MediaCommand            = ucfirst(strtolower(Option::getValue('cmd', true)));
    //     $params['Command']       = $MediaCommand;
    //     $params['CMD_CLASS_DIR'] = ucfirst($CommandName);

    //     $params['CommandClass']   = $CommandFileName;
    //     $params['CommandName']    = $CommandName;
    //     $params['CommandDesc']    = Option::getValue('desc', true);
    //     $params['CommandMethods'] = $this->getMethods(Option::getValue('method'));

    //     $template = $this->template($this->CMD_TEMPLATE, $params);

    //     $this->COMMAND_PATH = $this->parse($this->COMMAND_PATH, ['CMD_NAME' => ucfirst($MediaCommand)]);
    //     $CommandDir         = $this->COMMAND_PATH . '/' . ucfirst($CommandName);
    //     FileSystem::createDir($CommandDir);
    //     $CommandFileName = $CommandDir . DIRECTORY_SEPARATOR . $CommandFileName . '.php';

    //     if (file_exists($CommandFileName)) {
    //         FileSystem::delete($CommandFileName);
    //     }

    //     FileSystem::write($CommandFileName, $template);
    // }

    // private function getMethods($methodArray)
    // {
    //     $text = [];
    //     foreach ($methodArray as $cmd) {
    //         $text[] = "'" . $cmd . "' => null,";
    //     }

    //     return implode(PHP_EOL, $text);
    // }

    // public function template($template, $params = [])
    // {
    //     // utminfo(func_get_args());

    //     $template_text = $this->loadTemplate($template);

    //     return $this->parse($template_text, $params);
    // }

    // public function callback_replace($matches)
    // {
    //     // utminfo(func_get_args());

    //     return '';
    // }

    // private function loadTemplate($template)
    // {
    //     // utminfo(func_get_args());

    //     if ($template !== null) {
    //         $template_file = $template;

    //         return file_get_contents($template_file);
    //     }

    //     return null;
    // }

    // private function parse($text, $params = [])
    // {
    //     // utminfo(func_get_args());

    //     if (is_array($params)) {
    //         foreach ($params as $key => $value) {
    //             $key = '%%' . strtoupper($key) . '%%';
    //             // // utmdump([$text,$value,$key]);
    //             $text = str_replace($key, $value, $text);
    //         }

    //         $text = preg_replace_callback('|%%(\w+)%%|i', [$this, 'callback_replace'], $text);
    //     }

    //     return $text;
    // }
}
