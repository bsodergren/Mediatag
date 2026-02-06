<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['Default', 'Display', 'Question'];

    public function Definitions()
    {
        // utminfo();

        self::$Class = __CLASS__;

        return [
            ['extension', 'e', InputOption::VALUE_OPTIONAL, self::text('L__CONVERT_APPROVE_CHANGES')],
            // ['convert', 'U', InputOption::VALUE_NONE, self::text('L__CONVERT_ALL_TAGS')],
            // ['clear', '', InputOption::VALUE_NONE, self::text('L__CONVERT_CLEAR')],

            // ['rename', 'R', InputOption::VALUE_NONE, self::text('L__CONVERT_ALL_TAGS')],
            // ['addClass', 'C', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__CONVERT_ALL_TAGS')],
            // ['addNetwork', 'P', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__CONVERT_ALL_TAGS')],
            // ['cache', '', InputOption::VALUE_NONE, self::text('L__CONVERT_NEWFILES_REPLACEMENT')],
            // // ['cacheConvert', 'u', InputOption::VALUE_NONE, self::text('L__CONVERT_ALL_TAGS')],
            // ['break'],
        ];
    }

    // public function optionClosure($input,$option)
    // {

    //     // utmdump(["Update Option",$option,__META_TAGS__]);
    // }
}
