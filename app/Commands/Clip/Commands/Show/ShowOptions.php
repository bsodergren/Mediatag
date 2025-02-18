<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShowOptions extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = [];

    // public function Definitions()
    // {

    //     Translate::$Class = __CLASS__;

    //     return [
    //         ['time','',InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
    //         ['name', '', InputOption::VALUE_OPTIONAL| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
    //         ['type','t', InputOption::VALUE_REQUIRED| InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
    //         ['dur','D',InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
    //     ];
    // }
    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
