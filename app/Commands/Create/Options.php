<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Traits\Translate;
use Mediatag\Core\MediaOptions;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    // public $options = ['Test'];
    public $options = ['Default'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
           
            ['command', 'c', InputOption::VALUE_REQUIRED, Translate::text('L__DB_MARKERS_UPDATE')],
            ['name', '', InputOption::VALUE_REQUIRED, Translate::text('L__DB_FILE_UPDATE')],
            ['desc', 'd', InputOption::VALUE_REQUIRED, Translate::text('L__DB_FILE_UPDATE')],
            
            ['break'],

          
        ];
    }

    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
