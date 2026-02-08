<?php

namespace Mediatag\Commands\Create\Commands\Add;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use Mediatag\Commands\Create\ClassMethods;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\DynamicProperty;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\TraitType;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

use function is_array;

trait AddHelper
{
    use ClassMethods;
    use DynamicProperty;

    public function addCommand()
    {
        $this->parseOptions();
        $this->getClassBase();
        $this->SetNameSpace();
        $this->getUseClasses();
        $this->getTraits();

        $method = 'add' . $this->type;
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            Mediatag::$output->writeln('Method not found: ' . $method);
        }
        $this->saveClass();

        utmdd(get_class_vars(get_class($this)), Option::getOptions());
    }

    public function addProcess()
    {
        Mediatag::$output->writeln('Add ddd command2');
        // utmdd(Option::getOptions());
    }

    public function addHelper()
    {
        $this->addMethods();
        Mediatag::$output->writeln('Add ddd command3');
        // utmdd(Option::getOptions());
    }

    public function addOptions()
    {
        Mediatag::$output->writeln('Add ddd command4');
        // utmdd(Option::getOptions());
    }
}
