<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    public $options = ['Default', 'Meta'];

    public function Definitions()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        Translate::$Class = __CLASS__;

        return [
            ['missing', 'm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__SHOW_MISSING'), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['new', '', InputOption::VALUE_NONE, Translate::text('L__SHOW_NEW')],
            ['playlist', '', InputOption::VALUE_REQUIRED, Translate::text('L__SHOW_PLAYLIST')],
            ['duplicates', 'D', InputOption::VALUE_NONE, Translate::text('L__SHOW_DUPES')],
        ];
    }
}
