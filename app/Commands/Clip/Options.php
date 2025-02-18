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

    // public $options = ['Default','Question'];

    public function Definitions()
    {
        Translate::$Class = __CLASS__;

        return [
            ['name', '', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__CLIP_MERGED_NAME')],
        ];
    }

    public function Arguments($varName = null, $description = null)
    {
        // utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
