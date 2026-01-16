<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;

    public $options = ['Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['file', 'F', InputOption::VALUE_REQUIRED, Translate::text('L__PLAYLIST_FILE')],
            ['find', 'f', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_FIND')],
            ['watchlater', 'w', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_WATCHLATER')],
            ['url', 'u', InputOption::VALUE_REQUIRED, Translate::text('L__PLAYLIST_URL')],
            ['download', null, InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_DOWNLoAD')],
            ['premium', 'P', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_PREMIUM')],

            ['break'],
            ['archive', 'a', InputOption::VALUE_REQUIRED, Translate::text('L__PLAYLIST_ARCHIVE')],
            ['split', 'S', InputOption::VALUE_REQUIRED, Translate::text('L__PLAYLIST_SPLIT')],

            ['missing', 'm', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_MISSING')],
            ['max', 'M', InputOption::VALUE_REQUIRED, Translate::text('L__PLAYLIST_MAX')],
            ['json', 'j', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_JSON')],
            ['skip', 's', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_SKIP')],
            //  ['compact', 'c', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_COMPACT')],
            ['clean', null, InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_CLEAN')],
            ['ignore', 'i', InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_IGNORE')],
            ['debug', null, InputOption::VALUE_NONE, Translate::text('L__PLAYLIST_DEBUG')],
            ['range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L__DEFAULT_RANGE')],
        ];
    }
    //     public function Arguments($varName = null, $description = null)
    //     {
    //         // utminfo(func_get_args());
    //
    //         return [$varName, InputArgument::OPTIONAL, $description];
    //     }
}
