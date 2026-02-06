<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['Default', 'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['json', 'j', InputOption::VALUE_NONE, self::text('L__DOWNLOAD_MOVE')],
            ['convert', '', InputOption::VALUE_NONE, self::text('L__DOWNLOAD_CONVERT')],
        ];
    }
}
