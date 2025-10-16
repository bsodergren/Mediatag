<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Resize;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Clip\Helper;
use Mediatag\Commands\Clip\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'resize', description: 'Merge Command')]
class ResizeCommand extends MediaCommand
{
    // use Lang;
    //
    // use Helper;
    public const USE_LIBRARY = true;

    // public const SKIP_SEARCH = true;

    public $command = [
        'resize' => [
              'exec'              => null,
              'init' => null,
              'resizeFile' => null],
    ];
    // public $command = [
    //     'merge'           => [
    //         'exec'            => null,
    //         'getfileList'     => null,
    //         'mergeClips'      => null,
    //     ],
    // ];

    // public function MergeFunction()
    // {
    //     $list = $this->getfileList();
    //     utmdd($list);
    // }
}
