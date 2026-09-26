<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create\Commands\Add;

use Mediatag\Commands\Create\ClassMethods;
use Mediatag\Core\Mediatag;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\TraitType;
use Nette\Utils\FileSystem;
use UTM\Utilities\DynamicProperty;
use UTM\Utilities\Option;

use function is_array;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

trait AddHelper
{
    use ClassMethods;
    use DynamicProperty;


    private function createClassFile()
    {
        $fileType = ucfirst($this->type);
        $methods = $this->functions[$fileType];

            utmdump(['createClassFile'=>$methods]);


        // $this->parseOptions($fileType);
            foreach ($methods as $method) {
                            utmdump([$fileType,$method]);

                $this->$method();
            }
    }

    public function addCommandClass()
    {
                $this->parseOptions();

        $method = 'add' . $this->type;
        utmdump(['addCommandClass' => $method]);
         $this->createClassFile();

         if (method_exists($this, $method)) {
            $this->$method();
        } else {
            Mediatag::$output->writeln('Method not found: ' . $method);
        }
        $this->saveClass();

        // utmdd(get_class_vars(get_class($this)), Option::getOptions());
    }


    public function addCommand()
    {
        Mediatag::$output->writeln('Add ddd command');
    }

    public function addProcess()
    {
        Mediatag::$output->writeln('Add ddd command2');
        // utmdd(Option::getOptions());
    }

    public function addHelper()
    {
        Mediatag::$output->writeln('Add ddd command3');
        // utmdd(Option::getOptions());
    }

    public function addOptions()
    {
        Mediatag::$output->writeln('Add ddd command4');
        // utmdd(Option::getOptions());
    }
}
