<?php

namespace Mediatag\Commands\Playlist\Commands\Find;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Commands\Playlist\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FindOptions extends Options
{
    use Lang;
    use Translate;

    public $options = []; //['Test'];

    public function Definitions()
    {
        self::$Class = __CLASS__;
        // $parentOptions = parent::Definitions();
        $options = [

            ['existing', 'e', InputOption::VALUE_NONE, self::text('L__PLAYLIST_FIND')],
            ['missing', 'm', InputOption::VALUE_NONE, self::text('L__PLAYLIST_MISSING')],
            ['json', 'j', InputOption::VALUE_NONE, self::text('L__PLAYLIST_JSON')],
            // ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L_OPTION_OVERWRITE')],
            ['break'],
        ];

        return $options;
        // return array_merge($parent,Options $options);
    }
}
