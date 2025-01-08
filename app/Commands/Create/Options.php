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
    public $options = [];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['cmd', 'c', InputOption::VALUE_REQUIRED, Translate::text('L__DB_MARKERS_UPDATE')],
            ['name', '', InputOption::VALUE_REQUIRED, Translate::text('L__DB_FILE_UPDATE')],
            ['desc', 'd', InputOption::VALUE_REQUIRED, Translate::text('L__DB_FILE_UPDATE')],
            ['method', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DB_FILE_UPDATE')],
            ['options', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DB_FILE_UPDATE')],

            ['break'],
        ];
    }

    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
