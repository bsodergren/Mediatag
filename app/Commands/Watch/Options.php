<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Watch;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    // public $options                          = ['Default'=>false];
    // public $options = ['Default'=>true, 'Test'=>true];
    public $options = ['Default',  'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['colors', 'C', InputOption::VALUE_NONE, self::text('L__TEST_CLIP')],
            ['move', 'm', InputOption::VALUE_NONE, self::text('L__TEST_MOVE')],

            ['cmd', 'c', InputOption::VALUE_REQUIRED, self::text('L__TEST_CLIP')],
            ['name', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
        ];
    }
}
