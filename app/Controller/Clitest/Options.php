<?php

namespace Mediatag\Controller\Clitest;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    const USE_LIBRARY = 'FFMpeg';

    const USE_SEARCH = true;

    // public $options = ['Default'];
    public $options = ['CliCmd']; // ['Default'];

    // public function __call($name, $arguments)
    // {
    //     utmdd([$name, $arguments]);
    //     throw new \Exception('Not implemented');
    // }
    public function progressbarOptions()
    {
        self::$Class = __CLASS__;

        //  ->addOption('case', 'c', InputOption::VALUE_REQUIRED, 'Case name.')
        //         ->addOption('no-sleep', 't', InputOption::VALUE_NONE, 'Disable sleep timer.')
        //         ->addOption('break');
        //     parent::configure();
        return [
            ['case', 'c', InputOption::VALUE_REQUIRED, 'Case name.'],
            ['no-sleep', 't', InputOption::VALUE_NONE, 'Disable sleep timer.'],
        ];
    }
}
