<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Traits\Translate;
use Mediatag\Core\MediaOptions;
use Mediatag\Commands\Clip\Lang;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
   
    public $options = ['Default'];

    public function Definitions()
    {

        Translate::$Class = __CLASS__;

        return [
            ['clip', 'C', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_CREATE_CLIPS')],
            ['convert', 'c', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_CREATE_COMP')],
            ['delete', 'd', InputOption::VALUE_NONE, Translate::text('L__CLIP_DELETE_CLIPS')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__CLIP_DELETE_YES')],
        ];
    }
}
