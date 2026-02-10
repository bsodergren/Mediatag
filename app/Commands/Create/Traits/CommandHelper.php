<?php

namespace Mediatag\Commands\Create\Traits;

use Mediatag\Core\Mediatag;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\TraitType;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

trait CommandHelper
{
    private $newCmdClass;

    private $newCmdNamespace;

    public function createCommandFile()
    {
        //$className         = $this->CommandNameSpace . '\\Command';
        $this->newCmdClass = new ClassType('Command');

        $this->newCmdClass
            ->setExtends('Mediatag\Core\MediaCommand');

        $this->newCmdClass->addAttribute('Symfony\Component\Console\Attribute\AsCommand', ['name' => $this->cmd, 'description' => 'Command description']);
        $this->newCmdNamespace = new \Nette\PhpGenerator\PhpNamespace($this->CommandNameSpace);

        $this->newCmdNamespace->addUse('Mediatag\Core\MediaCommand');
        $this->newCmdNamespace->addUse('Symfony\Component\Console\Attribute\AsCommand');

        $this->newCmdClass->addConstant('USE_LIBRARY', false);
        $this->newCmdClass->addConstant('USE_SEARCH', true);

        $this->newCmdClass->addProperty('command')
            ->setPublic()
            ->setValue([
                lcfirst($this->cmd) => ['exec' . $this->cmd => null],
            ]);
        $this->newCmdNamespace->add($this->newCmdClass);

        $this->saveCmdFiles($this->CommandFile);
    }

    public function createHelperFile()
    {
        //$className         = $this->CommandNameSpace . '\\Command';
        $this->newCmdClass = new TraitType('Helper');

        // $this->newCmdClass
        //     ->setExtends('Mediatag\Core\Mediatag');

        $this->newCmdNamespace = new \Nette\PhpGenerator\PhpNamespace($this->CommandNameSpace);
        $this->newCmdNamespace->addUse('Mediatag\Core\Mediatag');

        $method = $this->newCmdClass->addMethod('exec' . $this->cmd);

        $method->setPublic()->setBody(' Mediatag::$Console->writeln("Hello ". __METHOD__);' . PHP_EOL . 'exit;');

        $this->newCmdNamespace->add($this->newCmdClass);

        $this->saveCmdFiles($this->HelperFile);
    }

    public function createProcessFile()
    {
        //$className         = $this->CommandNameSpace . '\\Command';
        $this->newCmdClass = new ClassType('Process');

        $this->newCmdClass
            ->setExtends('Mediatag\Core\Mediatag');

        $this->newCmdNamespace = new \Nette\PhpGenerator\PhpNamespace($this->CommandNameSpace);

        $this->newCmdNamespace->addUse('Symfony\Component\Console\Input\InputInterface');
        $this->newCmdNamespace->addUse('Symfony\Component\Console\Output\OutputInterface');
        $this->newCmdNamespace->addUse('Mediatag\Core\Helper\MediaExecute');
        $this->newCmdNamespace->addUse('Mediatag\Core\Helper\MediaProcess');
        $this->newCmdNamespace->addUse('Mediatag\Core\Mediatag');
        $this->newCmdNamespace->addUse($this->CommandNameSpace . '\\Helper');

        $this->newCmdClass->addTrait('Mediatag\Core\Helper\MediaExecute');
        $this->newCmdClass->addTrait('Mediatag\Core\Helper\MediaProcess');
        $this->newCmdClass->addTrait($this->CommandNameSpace . '\\Helper');
        $this->newCmdClass->addProperty('useFuncs')
            ->setProtected()
            ->setValue(['addMeta', 'setupMap']);

        $method = $this->newCmdClass->addMethod('__construct');

        $method->setPublic()->setBody('parent::boot($input, $output);');
        $method->addParameter('input')->setType('Symfony\Component\Console\Input\InputInterface');
        $method->addParameter('output')->setType('Symfony\Component\Console\Output\OutputInterface');

        $this->newCmdNamespace->add($this->newCmdClass);

        $this->saveCmdFiles($this->ProcessFile);
    }

    public function createLangFile()
    {
        $this->newCmdClass = new TraitType('Lang');

        // $this->newCmdClass
        //     ->setExtends('Mediatag\Core\Mediatag');

        $this->newCmdNamespace = new \Nette\PhpGenerator\PhpNamespace($this->CommandNameSpace);

        $this->newCmdClass->addConstant('L_OPTION_OVERWRITE', 'Overwrite');

        $this->newCmdNamespace->add($this->newCmdClass);

        $this->saveCmdFiles($this->LangFile);
    }

    public function createOptionsFile()
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

        //$className         = $this->CommandNameSpace . '\\Command';
        $this->newCmdClass = new ClassType('Options');

        $this->newCmdClass
            ->setExtends('Mediatag\Core\MediaOptions');

        $this->newCmdNamespace = new \Nette\PhpGenerator\PhpNamespace($this->CommandNameSpace);

        $this->newCmdNamespace->addUse('Symfony\Component\Console\Input\InputOption');
        $this->newCmdNamespace->addUse('Symfony\Component\Console\Input\InputArgument');
        $this->newCmdNamespace->addUse('Mediatag\Traits\Translate');
        $this->newCmdNamespace->addUse('Mediatag\Core\MediaOptions');
        $this->newCmdNamespace->addUse($this->CommandNameSpace . '\\Lang');

        $this->newCmdClass->addTrait('Mediatag\Traits\Translate');
        $this->newCmdClass->addTrait($this->CommandNameSpace . '\\Lang');

        $this->newCmdClass->addProperty('options')
            ->setPublic()
            ->setValue(['Test']);

        $method = $this->newCmdClass->addMethod('Definitions');

        $method->setPublic()->setBody($DefinitionBody);

        $this->newCmdNamespace->add($this->newCmdClass);

        $this->saveCmdFiles($this->OptionsFile);
    }
}
