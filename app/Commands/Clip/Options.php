<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    // public $options                          = ['Default'=>false];
    // public $options = ['Default'=>true, 'Test'=>true];
    public $options = ['Default'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['clip', 'C', InputOption::VALUE_NONE, Translate::text('L__CLIP_CREATE_CLIPS')],

            ['convert', 'c', InputOption::VALUE_REQUIRED, Translate::text('L__CLIP_CREATE_COMP')],
            ['delete', 'd', InputOption::VALUE_OPTIONAL, Translate::text('L__CLIP_DELETE_CLIPS')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__CLIP_DELETE_YES')],

        ];
    }
}
