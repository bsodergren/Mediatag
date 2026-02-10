<?php

namespace Mediatag\Commands\Create\Traits;

use Mediatag\Core\Mediatag;
use Nette\PhpGenerator\PsrPrinter;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

trait CreateBaseCommandHelper
{
    use CommandHelper;

    private $CommandFile;

    private $ProcessFile;

    private $HelperFile;

    private $OptionsFile;

    private $LangFile;

    private $CmdDir;

    private $CommandNameSpace;

    public function binFile()
    {
        $phpFile = <<<'EOT'
#!/usr/bin/env php
<?php
/**
 * Command like Metatag writer for video files.
 */

define('__ROOT_DIRECTORY__', dirname(realpath($_SERVER['SCRIPT_FILENAME']), 2));
define('__CONFIG_LIB__', __ROOT_DIRECTORY__.'/config');

require __ROOT_DIRECTORY__.'/bootstrap.php';

require __CONFIG_LIB__.'/applicationLoader.php';
EOT;

        return $phpFile;
    }

    public function createBinFile()
    {
        Mediatag::$output->writeln('create base command');

        $binFile = __APP_HOME__ . '/bin/media' . strtolower($this->cmd);
        if (! file_exists($binFile)) {
            FileSystem::write($binFile, $this->binFile());
            chmod($binFile, 0755);
        }
        //$this->addToCommandsConfig();
        $this->createCmdFiles();
    }

    private function addToCommandsConfig()
    {
        $configFile = __CONFIG_LIB__ . '/commands.php';
        $configText = file_get_contents($configFile);

        $className = ucfirst($this->cmd) . 'Command';

        $newCommandText = "    '" . $this->cmd . "'   => function () {\n        return new " . $className . ";\n    },\n";

        $this->CommandNameSpace = 'Mediatag\\Commands\\' . ucfirst($this->cmd);

        if (strpos($configText, $newCommandText) === false) {
            $configText = str_replace('// %%NEW_USE%%', 'use Mediatag\\Commands\\' . $this->cmd . '\\Command as ' . $className . ";\n// %%NEW_USE%%", $configText);
            $configText = str_replace('// %%NEW_COMMAND%%', $newCommandText . '    // %%NEW_COMMAND%%', $configText);
            file_put_contents($configFile, $configText);
        }
    }

    private function createCmdFiles()
    {
        $this->CmdDir = __COMMANDS_DIR__ . DIRECTORY_SEPARATOR . ucfirst($this->cmd);
        if (! is_dir($this->CmdDir)) {
            mkdir($this->CmdDir, 0777, true);
        }

        $this->CommandFile = $this->CmdDir . DIRECTORY_SEPARATOR . 'Command' . '.php';
        $this->ProcessFile = $this->CmdDir . DIRECTORY_SEPARATOR . 'Process' . '.php';
        $this->HelperFile  = $this->CmdDir . DIRECTORY_SEPARATOR . 'Helper' . '.php';
        $this->OptionsFile = $this->CmdDir . DIRECTORY_SEPARATOR . 'Options' . '.php';
        $this->LangFile    = $this->CmdDir . DIRECTORY_SEPARATOR . 'Lang' . '.php';
        $this->createCommandFile();
        $this->createHelperFile();
        $this->createOptionsFile();
        $this->createLangFile();
        $this->createProcessFile();
    }

    private function saveCmdFiles($path)
    {
        $printer = new PsrPrinter;

        $fileString = $printer->printNamespace($this->newCmdNamespace);
        $fileString = "<?php\n\n" . $fileString;

        $filename = str_replace(__COMMANDS_DIR__, '', $path);

        $overwrite = true;
        if (file_exists($path)) {
            if (Option::isFalse('overwrite')) {
                Mediatag::$output->writeln('File already exists: ' . $filename);
                Mediatag::$output->writeln('Use --overwrite option to overwrite the existing file.');
                $overwrite = false;
            }
        }
        if ($overwrite === true) {
            FileSystem::write($path, $fileString);
            Mediatag::$output->writeln('Class saved to: ' . $filename);
            // Mediatag::$output->writeln($fileString);
        }

        unset($this->newCmdNamespace);
        unset($this->newCmdClass);
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

        if ($template !== null) {
            $template_file = $template;

            return file_get_contents($template_file);
        }

        return null;
    }

    private function parse($text, $params = [])
    {
        // utminfo(func_get_args());

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $key = '%%' . strtoupper($key) . '%%';
                // // utmdump([$text,$value,$key]);
                $text = str_replace($key, $value, $text);
            }

            $text = preg_replace_callback('|%%(\w+)%%|i', [$this, 'callback_replace'], $text);
        }

        return $text;
    }
}
