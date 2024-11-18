<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;

const DESCRIPTION = 'Updates metatags on files';
const NAME        = 'update';

use UTM\Utilities\Option;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public $process;

    public static $AskQuestion = 'question';

 
    

    /*
    public function execute(InputInterface $input, OutputInterface $output): int
    {
 // utminfo(func_get_args());


        if (true == Option::isTrue('rename')) {
            $options = Option:: getOptions();
            //utmdd($options);

            $greetInput = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'rename',
                '-R'  => true,
            ]);

            $returnCode = $this->getApplication()->doRun($greetInput, $output);
            return SymCommand::SUCCESS;
        } else {

        parent::execute($input, $output);

        return SymCommand::SUCCESS;
        }
    }
    */

}
