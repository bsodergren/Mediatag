<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['Default', 'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['depth', 'D', InputOption::VALUE_REQUIRED, self::text('L__TEST_TITLE')],
            ['byGenre', 'g', InputOption::VALUE_NONE, self::text('L__TEST_MOVE_FILES')],
            ['byStudio', 's', InputOption::VALUE_REQUIRED, self::text('L__TEST_TITLE')],
            ['lowercase', 'l', InputOption::VALUE_NONE, self::text('L__RENAME_LOWER')],
            ['backup', 'b', InputOption::VALUE_NONE, self::text('L__RENAME_TRANS')],

            ['trans', 't', InputOption::VALUE_REQUIRED, self::text('L__RENAME_TRANS')],
        ];

        // return array_merge( parent::getMetaOptions(),$options);
    }

    // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return [$varName, InputArgument::OPTIONAL, $description];
    // }
}
