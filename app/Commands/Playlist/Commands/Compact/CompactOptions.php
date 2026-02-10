<?php

namespace Mediatag\Commands\Playlist\Commands\Compact;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Commands\Playlist\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CompactOptions extends Options
{
    use Lang;
    use Translate;

    public $options = ['Test'];

    public function Definitions()
    {
        self::$Class   = __CLASS__;
                $parentOptions = parent::Definitions();
                $options       = [
                    // ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L_OPTION_OVERWRITE')],
                    ['break'],
                ];

                return array_merge($parentOptions, $options);
    }
}
