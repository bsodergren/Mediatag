<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

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
        // utminfo(func_get_args());

        self::$Class = __CLASS__;

        return [
            ['missing', 'm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, self::text('L__SHOW_MISSING'), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['new', '', InputOption::VALUE_NONE, self::text('L__SHOW_NEW')],
            ['playlist', '', InputOption::VALUE_REQUIRED, self::text('L__SHOW_PLAYLIST')],
            ['duplicates', 'D', InputOption::VALUE_NONE, self::text('L__SHOW_DUPES')],
        ];
    }
}
