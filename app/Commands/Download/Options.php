<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    public function Definitions()
    {
        utminfo();

        Translate::$Class = __CLASS__;

        return [
            ['json', 'j', InputOption::VALUE_NONE, Translate::text('L__DOWNLOAD_MOVE')],
            ['convert', '', InputOption::VALUE_NONE, Translate::text('L__DOWNLOAD_CONVERT')],
        ];
    }
}
