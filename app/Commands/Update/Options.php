<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    public $options = ['Default', 'Meta', 'Display', 'Test'];

    public function Definitions()
    {
        // utminfo();

        Translate::$Class = __CLASS__;

        return [
            ['changes', 'c', InputOption::VALUE_NONE, Translate::text('L__UPDATE_APPROVE_CHANGES')],
            ['update', 'U', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['clear', '', InputOption::VALUE_NONE, Translate::text('L__UPDATE_CLEAR')],

            ['rename', 'R', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['addClass', 'C', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__UPDATE_ALL_TAGS')],
            ['addNetwork', 'P', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__UPDATE_ALL_TAGS')],
            ['cache', '', InputOption::VALUE_NONE, Translate::text('L__UPDATE_NEWFILES_REPLACEMENT')],
            // ['cacheUpdate', 'u', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['break'],
            ['download', 'D', InputOption::VALUE_NONE, Translate::text('L__UPDATE_DOWNloAD')],
        ];
    }

    // public function optionClosure($input,$option)
    // {

    //     utmdump(["Update Option",$option,__META_TAGS__]);
    // }
}
