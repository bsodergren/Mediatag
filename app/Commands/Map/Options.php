<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    public $options                          = ['Default' => false,'Meta'=>false,'Test'=>false,'Display' => true];

    public function Definitions()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        Translate::$Class = __CLASS__;
        return [
            ['lang', 'l',  InputOption::VALUE_REQUIRED, Translate::text('L__MAP_LANG')],
            ['file', 'f',  InputOption::VALUE_REQUIRED, Translate::text('L__MAP_FILE')],
            ['channel', 'c',  InputOption::VALUE_REQUIRED, Translate::text('L__MAP_CHANNEL'), [__CHANNELS__]],
            ['break'],

            ['studio', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_STUDIOS'), []],
            ['artist', 'a', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_ARTIST'), []],
            // ['title', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_TITLE'), []],
            ['genre', 'g', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_GENRE'), []],
            ['keyword', 'k', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_KEYWORD'), []],
            ['list', '', InputOption::VALUE_REQUIRED, Translate::text('L__MAP_LIST')],
            ['word','w', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_WORD'), []],
            ['artistMap','M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__MAP_WORD'), []],
            ['break'],

            ['video', '', InputOption::VALUE_OPTIONAL, Translate::text('L__MAP_VIDEO')],
            ['search', '', InputOption::VALUE_REQUIRED, Translate::text('L__MAP_SEARCH')],
            ['replacement', 'R', InputOption::VALUE_REQUIRED, Translate::text('L__MAP_REPLACEMENT')],
            ['dir', 'D', InputOption::VALUE_REQUIRED, Translate::text('L__MAP_DIR')],
            ['empty', 'e', InputOption::VALUE_NONE, Translate::text('L__MAP_EMPTY')],

            ['break'],

        ];
    }
}
