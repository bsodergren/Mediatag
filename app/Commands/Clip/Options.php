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
   
    public $options = ['Default','Question'];

    public function Definitions()
    {

        Translate::$Class = __CLASS__;

        return [
            ['add','',InputOption::VALUE_NONE, Translate::text('L__CLIP_MERGED_NAME')],
            ['time','',InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
            ['name', '', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
            ['create', 'c', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_CREATE_CLIPS')],
            ['merge', 'm', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_CREATE_COMP')],
            ['delete', 'd', InputOption::VALUE_NONE, Translate::text('L__CLIP_DELETE_CLIPS')],
        ];
    }
}
