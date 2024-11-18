<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    // public $options = ['Test'];
    public $options = ['Default', 'Display', 'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['thumbnail', 't', InputOption::VALUE_NONE, Translate::text('L__DB_THUMBNAIL_UPDATE')],
            ['videopreview', 'P', InputOption::VALUE_NONE, Translate::text('L__DB_VPREVIEW_UPDATE')],
            ['json', 'j', InputOption::VALUE_NONE, Translate::text('L__DB_JSON_UPDATE')],
            ['duration', 'D', InputOption::VALUE_NONE, Translate::text('L__DB_DURATION_UPDATE')],
            ['info', 'i', InputOption::VALUE_NONE, Translate::text('L__DB_FILEINFO_UPDATE')],
            ['all', 'a', InputOption::VALUE_NONE, Translate::text('L__DB_ADD')],
            ['markers', 'm', InputOption::VALUE_NONE, Translate::text('L__DB_MARKERS_UPDATE')],
            ['break'],
            ['update', 'u', InputOption::VALUE_NONE, Translate::text('L__DB_FILE_UPDATE')],
            ['clean', 'c', InputOption::VALUE_NONE, Translate::text('L__DB_THUMBNAIL_CLEAN')],
            ['empty', 'e', InputOption::VALUE_NONE, Translate::text('L__DB_EMPTY')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__DB_YES')],
        ];
    }
}
