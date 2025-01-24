<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

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

        Translate::$Class = __CLASS__;

        return [
            ['colors', 'C', InputOption::VALUE_NONE, Translate::text('L__TEST_CLIP')],
            ['cmd', 'c', InputOption::VALUE_REQUIRED, Translate::text('L__TEST_CLIP')],
            ['name', '', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],


        ];
    }
}
