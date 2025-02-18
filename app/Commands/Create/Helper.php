<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\Mediatag;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

trait Helper
{
    private $CMD_TEMPLATE = __DATA_TEMPLATES__.'/Command/Command_template.txt';
    public $COMMAND_PATH  = __APP_HOME__.'/app/Commands/%%CMD_NAME%%/Commands';
    public $LANGUAGE_FILE = __APP_HOME__.'/app/Commands/%%CMD_NAME%%/Lang.php';
    public $GLOBAL_LANG   = __APP_HOME__.'/app/Locales/Lang.php';

    public function createCommand()
    {
        utmdd([__METHOD__, Option::getOptions()]);
    }

    public function langCommand()
    {
        $translationKey  = Option::getValue('name', true);
        $CommandFileName = Option::getValue('cmd', true);
        $desc            = Option::getValue('desc', true);

        $string = "\t".'public const '.$translationKey.'            = \''.$desc.'\';';

        $MediaCommand = ucfirst(strtolower(Option::getValue('cmd', true)));

        if ('Global' == $MediaCommand) {
            $this->LANGUAGE_FILE = $this->GLOBAL_LANG;
        } else {
            $this->LANGUAGE_FILE = $this->parse($this->LANGUAGE_FILE, ['CMD_NAME'=>ucfirst($MediaCommand)]);
        }

        $lines = FileSystem::readLines($this->LANGUAGE_FILE);
        foreach ($lines as $lineNum => $line) {
            if (str_contains($line, $translationKey)) {
                Mediatag::$output->writeln('already tehre');

                return false;
            }
            if ('}' == $line) {
                $newFile[] = $string;
                $newFile[] = '}';
                continue;
            }
            $newFile[] = $line;
        }
        $contents = implode("\n", $newFile);
        // utmdd($contents);
        FileSystem::write($this->LANGUAGE_FILE, $contents);

        utmdd($this->LANGUAGE_FILE);
    }

    public function addCommand()
    {
        // utmdd(Option::getOptions());
        $params          = [];
        $CommandName     = Option::getValue('name', true);
        $CommandFileName = ucfirst($CommandName.'Command');
        $desc            = Option::getValue('desc', true);
        if (null === $desc) {
            $desc = $CommandName.' command';
        }

        $params['USELIBRARY'] = 'true';
        $params['SKIPSEARCH'] = 'false';

        if (false === Option::isTrue('Library')) {
            $params['USELIBRARY'] = 'false';
        }
        if (false === Option::isTrue('Search')) {
            $params['SKIPSEARCH'] = 'true';
        }

        $MediaCommand            = ucfirst(strtolower(Option::getValue('cmd', true)));
        $params['Command']       = $MediaCommand;
        $params['CMD_CLASS_DIR'] = ucfirst($CommandName);

        $params['CommandClass']   = $CommandFileName;
        $params['CommandName']    = $CommandName;
        $params['CommandDesc']    = Option::getValue('desc', true);
        $params['CommandMethods'] = $this->getMethods(Option::getValue('method'));

        $template = $this->template($this->CMD_TEMPLATE, $params);

        $this->COMMAND_PATH = $this->parse($this->COMMAND_PATH, ['CMD_NAME'=>ucfirst($MediaCommand)]);
        $CommandDir         = $this->COMMAND_PATH.'/'.ucfirst($CommandName);
        FileSystem::createDir($CommandDir);
        $CommandFileName = $CommandDir.\DIRECTORY_SEPARATOR.$CommandFileName.'.php';

        if (file_exists($CommandFileName)) {
            FileSystem::delete($CommandFileName);
        }

        FileSystem::write($CommandFileName, $template);
    }

    private function getMethods($methodArray)
    {
        $text = [];
        foreach ($methodArray as $cmd) {
            $text[] = "'".$cmd."' => null,";
        }

        return implode(\PHP_EOL, $text);
    }

    public function template($template, $params = [])
    {
        // utminfo(func_get_args());

        $template_text = $this->loadTemplate($template);

        return $this->parse($template_text, $params);
    }

    public function callback_replace($matches)
    {
        // utminfo(func_get_args());

        return '';
    }

    private function loadTemplate($template)
    {
        // utminfo(func_get_args());

        if (null !== $template) {
            $template_file = $template;

            return file_get_contents($template_file);
        }

        return null;
    }

    private function parse($text, $params = [])
    {
        // utminfo(func_get_args());

        if (\is_array($params)) {
            foreach ($params as $key => $value) {
                $key = '%%'.strtoupper($key).'%%';
                // utmdump([$text,$value,$key]);
                $text = str_replace($key, $value, $text);
            }

            $text = preg_replace_callback('|%%(\w+)%%|i', [$this, 'callback_replace'], $text);
        }

        return $text;
    }
}
