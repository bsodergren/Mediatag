<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Controller\Rename\Options;

use Mediatag\Controller\Rename\Lang;
use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MoveOptions extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['CliCmd']; //['Default', 'Test'];

    // progressbarOptions
    public function moveOptions()
    {
        self::$Class = __CLASS__;

        return [['genre', 'g', InputOption::VALUE_NONE, self::text('L__TEST_MOVE_FILES')],
            ['studio', 's', InputOption::VALUE_REQUIRED, self::text('L__TEST_TITLE')], ];
    }

    public function Definitions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['depth', 'D', InputOption::VALUE_REQUIRED, self::text('L__TEST_TITLE')],

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
