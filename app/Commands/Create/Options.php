<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    // public $options = ['Test'];
    // public $options = ['question'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['userCommand', 'u', InputOption::VALUE_REQUIRED, self::text('L__DB_MARKERS_UPDATE')],
            ['cmd', 'c', InputOption::VALUE_REQUIRED, self::text('L__DB_MARKERS_UPDATE')],
            ['name', 'n', InputOption::VALUE_REQUIRED, self::text('L__DB_FILE_UPDATE')],
            ['desc', 'd', InputOption::VALUE_REQUIRED, self::text('L__DB_FILE_UPDATE')],
            ['type', 't', InputOption::VALUE_REQUIRED, self::text('L__DB_MARKERS_UPDATE')],
            ['exclude', 'e',  InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DB_MARKERS_UPDATE')],

            ['CmdMethod', 'm', InputOption::VALUE_REQUIRED, self::text('L__DB_FILE_UPDATE')],
            ['params', 'P', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DB_FILE_UPDATE')],
            ['break'],
            ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L__DB_FILE_UPDATE')],
        ];
    }

    // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return [$varName, InputArgument::OPTIONAL, $description];
    // }
}
