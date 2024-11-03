<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Mediatag;
use Mediatag\Traits\Translate;
use Mediatag\Core\MediaOptions;
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
        utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [

            ['output', 'o', InputOption::VALUE_REQUIRED, Translate::text('L__SHOW_PLAYLIST')],
        ];
    }
}
