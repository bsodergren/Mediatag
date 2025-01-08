<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Merge;

use Mediatag\Traits\Translate;
use Mediatag\Core\MediaOptions;
use Mediatag\Commands\Clip\Lang;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MergeOptions extends MediaOptions
{
    use Lang;
    use Translate;
   
    // public $options = ['Default','Question'];






    public function Definitions()
    {

        Translate::$Class = __CLASS__;

        return [

            ['name', '', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
            ['type','t', InputOption::VALUE_REQUIRED| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
            ['search','s', InputOption::VALUE_REQUIRED, Translate::text('L__CLIP_MERGED_NAME')],

            ['dur','D',InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
        ];
    }
    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
