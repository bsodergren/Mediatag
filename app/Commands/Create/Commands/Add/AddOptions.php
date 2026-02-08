<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create\Commands\Add;

use Mediatag\Commands\Create\Lang;
use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class AddOptions extends MediaOptions
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
            ['cmd', 'c', InputOption::VALUE_REQUIRED, self::text('L__DB_MARKERS_UPDATE')],
            ['type', 't', InputOption::VALUE_REQUIRED, self::text('L__DB_MARKERS_UPDATE')],
            ['name', 'n', InputOption::VALUE_REQUIRED, self::text('L__DB_FILE_UPDATE')],
            ['desc', 'd', InputOption::VALUE_REQUIRED, self::text('L__DB_FILE_UPDATE')],
            ['CmdMethod', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DB_FILE_UPDATE')],
            ['options', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__DB_FILE_UPDATE')],
            ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L__DB_FILE_UPDATE')],

            ['break'],
        ];
    }

    // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return [$varName, InputArgument::OPTIONAL, $description];
    // }
}
