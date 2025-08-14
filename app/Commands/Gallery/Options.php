<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    // public $options = ['Test'];
    public $options = ['Default', 'Display', 'Test'];

    public function Definitions()
    {
        // utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['info', 'i', InputOption::VALUE_NONE, Translate::text('L__GALLERY_FILEINFO_UPDATE')],
            ['break'],
            ['update', 'u', InputOption::VALUE_NONE, Translate::text('L__GALLERY_FILE_UPDATE')],
            ['clean', 'c', InputOption::VALUE_NONE, Translate::text('L__GALLERY_THUMBNAIL_CLEAN')],
            ['empty', 'e', InputOption::VALUE_NONE, Translate::text('L__GALLERY_EMPTY')],
            ['yes', 'y', InputOption::VALUE_NONE, Translate::text('L__GALLERY_YES')],
        ];
    }
}
