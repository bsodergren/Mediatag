<?php

namespace Mediatag\Commands\Create;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Commands\Create\Traits\BaseCommand;
use Mediatag\Core\MediaCommand;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\DynamicProperty;
use Mediatag\Utilities\Chooser;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\TraitType;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

use function is_array;

trait ClassMethods
{
    public $cmd = null;

    public $type = null;

    public $name = null;

    public $desc = null;

    public $CmdMethod = null;

    public $options = [];

    public $className = null;

    public $userCommand = null;

    public $NewNamespace = null;

    private $GeneratedClass = null;

    public function parseOptions($type = null)
    {
        if ($type) {
            $this->type = $type;
        } else {
            $type = Option::getValue('type', true);
        }

        if ($type) {
            $this->type = ucfirst($type);
        } else {
            if (Option::getValue('command', true) == 'new') {
                $this->type = 'Command';
            } else {
                $this->type = 'Process';
            }
        }
        $this->userCommand = Option::getValue('userCommand', true);
        if ($this->userCommand === null) {
            $this->cmd = Option::getValue('cmd', true);
            if ($this->cmd === null) {
                Mediatag::$output->writeln('No cmd provided, exiting.');
                exit;
            }
            $this->name = Option::getValue('name', true);
            if ($this->name === null) {
                Mediatag::$output->writeln('No name provided, exiting.');
                exit;
            }
        } else {
            $parts = explode(':', $this->userCommand);

            // utmdd(['userCommand' => $this->userCommand, 'parts' => $parts]);
            // if (count($parts) !== 2 && count($parts) !== 3) {
            //     Mediatag::$output->writeln('Invalid user command format. Expected format: cmd:name:type');
            //     exit;
            // }
            $this->cmd  = $parts[0];
            $this->name = $this->cmd;
            if (count($parts) === 2) {
                $this->name = $parts[1];
            }
            if (count($parts) === 3) {
                $this->type = ucfirst($parts[2]);
            }
        }
        $this->desc = Option::getValue('desc', true);
        if ($this->desc === null) {
            $this->desc = 'Description for ' . ucfirst($this->name) . ' ' . $this->type;
        }
        $this->CmdMethod = Option::getValue('CmdMethod', true);
        if ($this->CmdMethod === null) {
            $this->CmdMethod = ucfirst($this->name) . 'Method';
        }

        $this->options = Option::getValue('options', true);

        $this->className = ucfirst($this->name) . ucfirst($this->type);

        if ($this->cmd) {
            $DefaultCommandFile = __COMMANDS_DIR__ . DIRECTORY_SEPARATOR . $this->cmd . DIRECTORY_SEPARATOR . 'Command' . '.php';
            if (! file_exists($DefaultCommandFile)) {
                Mediatag::$output->writeln('Oops Looks like we need to create the base command first: ' . $DefaultCommandFile);
                $this->createBinFile();
            }
        }
        utmdump([
            'defaultCommandFile' => isset($DefaultCommandFile) ? $DefaultCommandFile : null,
            'type'               => $this->type,
            'cmd'                => $this->cmd,
            'name'               => $this->name,
            'desc'               => $this->desc,
            'CmdMethod'          => $this->CmdMethod,
            'options'            => $this->options,
        ]);
    }

    private function addMethods()
    {
        $this->NewNamespace->addUse('Mediatag\Core\Mediatag');

        $method = $this->GeneratedClass->addMethod(lcfirst($this->CmdMethod));
        //->setPublic()->setBody('// ' . $this->CmdMethod . ' method body');
        $method->setPublic()->setBody(' Mediatag::$Console->writeln("Hello ". __METHOD__);' . PHP_EOL . 'exit;');
    }

    private function getClassBase()
    {
        switch ($this->type) {
            case 'Command':
            case 'Process':
            case 'Options':

                $this->GeneratedClass = new ClassType($this->className);
                $this->GeneratedClass->setExtends($this->getParentClass());

                return $this;
            case 'Helper':
                $this->GeneratedClass = new TraitType($this->className);

                return $this;
        }
    }

    private function getParentClass()
    {
        switch ($this->type) {
            case 'Command':
                return 'Mediatag\Core\MediaCommand';
                // return 'Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Command';
            case 'Process':
                return 'Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Process';
                // case 'Helper':
                //     return 'Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Helper';
            case 'Options':
                return 'Mediatag\\Core\\MediaOptions';
        }

        return null;
    }

    private function helperExists()
    {
        $traitName = ucfirst($this->name) . 'Helper';
        $path      = $this->getFilePath('Helper');

        if (file_exists($path)) {
            return 'Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Commands\\' . ucfirst($this->name) . '\\' . $traitName;
        }

        return false;
    }

    private function getUseClasses()
    {
        switch ($this->type) {
            case 'Command':
                $this->NewNamespace->addUse('Mediatag\Core\MediaCommand');
                $this->NewNamespace->addUse('Symfony\Component\Console\Attribute\AsCommand');
                $this->NewNamespace->addUse('Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Lang');

                return $this;
            case 'Process':
                if ($traitPath = $this->helperExists()) {
                    $this->NewNamespace->addUse($traitPath);
                }

                return $this;
            case 'Options':

                $this->NewNamespace->addUse('Symfony\Component\Console\Input\InputOption');
                $this->NewNamespace->addUse('Symfony\Component\Console\Input\InputArgument');
                $this->NewNamespace->addUse('Mediatag\Traits\Translate');
                $this->NewNamespace->addUse('Mediatag\Core\MediaOptions');
                $this->NewNamespace->addUse('Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Lang');

                return $this;

            default:
                return $this;
        }
    }

    private function getTraits()
    {
        switch ($this->type) {
            case 'Command':
                $this->GeneratedClass->addTrait('Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Lang');

                return $this;
            case 'Process':
                if ($traitPath = $this->helperExists()) {
                    $this->GeneratedClass->addTrait($traitPath);
                }

                return $this;
            case 'Options':

                $this->GeneratedClass->addTrait('Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Lang');
                $this->GeneratedClass->addTrait('Mediatag\Traits\Translate');

                return $this;

            default:
                return $this;
        }
    }

    private function SetNameSpace()
    {
        $namespace          = 'Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Commands\\' . ucfirst($this->name);
        $this->NewNamespace = new PhpNamespace($namespace);

        if ($use = $this->getParentClass()) {
            $this->NewNamespace->addUse($use);
        }
        $this->NewNamespace->add($this->GeneratedClass);

        return $this;
    }

    private function getCommandHeader()
    {
        $this->GeneratedClass->addComment($this->desc);
        $this->GeneratedClass->addComment('');
        $this->GeneratedClass->addComment('@package Mediatag\\Commands\\' . ucfirst($this->cmd) . '\\Commands\\' . ucfirst($this->name));
        $this->GeneratedClass->addComment('@version ' . date('Y-m-d H:i:s'));
        $this->GeneratedClass->addAttribute('Symfony\Component\Console\Attribute\AsCommand', [
            'name'        => lcfirst($this->name),
            'description' => $this->desc, ]);
    }

    private function addConstants()
    {
        $this->GeneratedClass->addConstant('USE_LIBRARY', false);
        $this->GeneratedClass->addConstant('SKIP_SEARCH', true);
    }

    private function addDefaultCommand()
    {
        $this->GeneratedClass->addProperty('command')
            ->setPublic()
            ->setValue([
                lcfirst($this->name) => [lcfirst($this->CmdMethod) => null],
            ]);
    }

    private function addOptionBody()
    {
        $DefinitionBody = <<<'EOT'
    self::$Class = __CLASS__;

    return [
        // ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L_OPTION_OVERWRITE')],
        // ['break'],
    ];
      // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return [$varName, InputArgument::OPTIONAL, $description];
    // }

EOT;
        $this->GeneratedClass->addProperty('options')
            ->setPublic()
            ->setValue(['Test']);

        $method = $this->GeneratedClass->addMethod('Definitions');

        $method->setPublic()->setBody($DefinitionBody);

        return $this;
    }

    private function saveClass()
    {
        $printer = new PsrPrinter;

        $fileString = $printer->printNamespace($this->NewNamespace);
        $fileString = "<?php\n\n" . $fileString;

        $path     = $this->getFilePath();
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
        }
        unset($this->NewNamespace);
        unset($this->GeneratedClass);
    }

    private function getFilePath($replacement = null)
    {
        if ($replacement) {
            $classFileName = str_replace('Process', $replacement, $this->className);
        } else {
            $classFileName = $this->className;
        }
        $path = __COMMANDS_DIR__ .
        DIRECTORY_SEPARATOR . $this->cmd .
        DIRECTORY_SEPARATOR . 'Commands' .
        DIRECTORY_SEPARATOR . ucfirst($this->name);
        if (! is_dir($path)) {
            FileSystem::createDir($path);
        }

        return $path . DIRECTORY_SEPARATOR . $classFileName . '.php';
    }
}
