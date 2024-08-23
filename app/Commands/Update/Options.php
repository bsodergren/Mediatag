<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    public $options = ['Default', 'Meta', 'Display', 'Test'];

    public function Definitions()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        Translate::$Class = __CLASS__;

        return [
            ['empty', 'e', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__UPDATE_EMPTYTAG'), [], ['Studio', 'Genre', 'Title', 'Artist', 'Keyword']],
            ['changes', 'c', InputOption::VALUE_NONE, Translate::text('L__UPDATE_APPROVE_CHANGES')],
            // ['list', 'l', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__UPDATE_LIST_CHANGES'), [], ['file']],
            ['update', 'U', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['rename', 'R', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['addClass', 'C', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, Translate::text('L__UPDATE_ALL_TAGS')],
            ['cache', '', InputOption::VALUE_NONE, Translate::text('L__UPDATE_NEWFILES_REPLACEMENT')],
            // ['cacheUpdate', 'u', InputOption::VALUE_NONE, Translate::text('L__UPDATE_ALL_TAGS')],
            ['break'],
            ['download', 'D', InputOption::VALUE_NONE, Translate::text('L__UPDATE_DOWNloAD')],
        ];
    }
}
