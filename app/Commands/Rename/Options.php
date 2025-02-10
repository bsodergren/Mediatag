<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Mediatag\Core\MediaOptions;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    public $options = ['Default', 'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['depth', 'D', InputOption::VALUE_REQUIRED, Translate::text('L__TEST_TITLE')],
            ['genre', 'g', InputOption::VALUE_NONE, Translate::text('L__TEST_MOVE_FILES')],
            ['studio', 's', InputOption::VALUE_REQUIRED, Translate::text('L__TEST_TITLE')],
            ['lowercase', 'l', InputOption::VALUE_NONE, Translate::text('L__RENAME_LOWER')],
            ['trans', 't', InputOption::VALUE_REQUIRED, Translate::text('L__RENAME_TRANS')],
        ];

        // return array_merge( parent::getMetaOptions(),$options);
    }

    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
