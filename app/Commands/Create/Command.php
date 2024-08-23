<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\Mediatag;

const DESCRIPTION = 'Create a new Command';
const NAME        = 'create';

use Mediatag\Core\MediaCommand;
use Mediatag\Traits\CmdCreater;
use Mediatag\Traits\MediaLibrary;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use CmdCreater;
    use Lang;
    use MediaLibrary;

    public $COMMAND_PATH = __APP_HOME__ . '/app/Commands';

    public $bin_path     = __APP_HOME__ . '/bin';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $CommandName                  = $input->getArgument(self::CMD_NAME);

        if (null === $CommandName) {
            utmdd([__METHOD__, 'NO CMD !!']);
        }
        $CommandClass                 = ucfirst(strtolower($CommandName));

        $CommandDir                   = $this->COMMAND_PATH . '/' . $CommandClass;

        if (is_dir($CommandDir)) {
            FileSystem::delete($CommandDir);
        }

        Filesystem::createDir($CommandDir, 0755);

        $binName                      = 'media' . strtolower($CommandClass);
        $binFile                      = $this->bin_path . '/' . $binName;

        $OptionsClass_file            = $CommandDir . '/Options.php';
        $CommandClass_file            = $CommandDir . '/Command.php';
        $LangClass_file               = $CommandDir . '/Lang.php';
        $ProcessClass_file            = $CommandDir . '/Process.php';

        $CommandNameSpace             = 'namespace Mediatag\\Commands\\' . $CommandClass;
        $CommandUse                   = 'use Mediatag\\Commands\\' . $CommandClass . '\\Command';
        $OptionsUse                   = 'use Mediatag\\Commands\\' . $CommandClass . '\\Options';
        $LangUse                      = 'use Mediatag\\Commands\\' . $CommandClass . '\\Lang';
        $ProcessUse                   = 'use Mediatag\\Commands\\' . $CommandClass . '\\Proccess';

        $params['CMD_NAMESPACE']      = $CommandNameSpace;
        $params['COMMAND_USE']        = $CommandUse;
        $params['OPTION_USE']         = $OptionsUse;
        $params['LANG_USE']           = $LangUse;
        $params['PROCESS_USE']        = $ProcessUse;
        $params['COMMAND_CLASS']      = $CommandClass;
        $params['COMMAND_NAME']       = $CommandName;

        $params['COMMAND_TEXT_NAME']  = strtoupper($CommandName);
        $params['COMMAND_CONST_DESC'] = strtoupper('L__' . $CommandName . '_DESC');

        $app                          = $this->template('app', $params);
        if (file_exists($binFile)) {
            FileSystem::delete($binFile);
        }

        FileSystem::write($binFile, $app, 0755);

        $cmd                          = $this->template('cmd', $params);
        $lang                         = $this->template('lang', $params);
        $opt                          = $this->template('opt', $params);
        $proc                         = $this->template('proc', $params);

        FileSystem::write($LangClass_file, $lang);
        FileSystem::write($CommandClass_file, $cmd);
        FileSystem::write($OptionsClass_file, $opt);
        FileSystem::write($ProcessClass_file, $proc);

        $this->AddCommand($params);

        return SymCommand::SUCCESS;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void {}
}
