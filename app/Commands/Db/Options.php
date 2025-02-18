<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
            ['json', 'j', InputOption::VALUE_NONE, Translate::text('L__DB_JSON_UPDATE')],
            ['all', 'a', InputOption::VALUE_NONE, Translate::text('L__DB_ADD')],
            ['markers', 'm', InputOption::VALUE_NONE, Translate::text('L__DB_MARKERS_UPDATE')],
            ['break'],
            ['update', 'u', InputOption::VALUE_NONE, Translate::text('L__DB_FILE_UPDATE')],
            ['clean', 'c', InputOption::VALUE_NONE, Translate::text('L__DB_THUMBNAIL_CLEAN')],
            ['delete', 'd', InputOption::VALUE_NONE, Translate::text('L__DB_THUMBNAIL_DELETE')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__DB_YES')],
        ];
    }

    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
