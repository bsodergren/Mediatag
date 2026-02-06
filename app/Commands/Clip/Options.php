<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    // public $options = ['Default'];
    public $options = ['Default'];

    public function Definitions()
    {
        self::$Class = __CLASS__;

        return [
            ['name', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
            ['time', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
            ['type', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
            ['search', 's', InputOption::VALUE_REQUIRED, self::text('L__CLIP_MERGED_NAME')],
            ['dur', 'D', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
            ['playlistid', 'P', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_NAME')],
            ['output', 'o', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_SCRIPT_OUTPUT')],
            ['dim', 'd', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::text('L__CLIP_MERGED_DIMENSION')],
        ];
    }

    // public function Arguments($varName = null, $description = null)
    // {
    //     // utminfo(func_get_args());

    //     return [$varName, InputArgument::OPTIONAL, $description];
    // }
}
