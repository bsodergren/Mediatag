<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Rotate;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Clip\Helper;
use Mediatag\Commands\Clip\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'Rotate', description: 'Merge Command')]
class RotateCommand extends MediaCommand
{
    // use Lang;
    //
    // use Helper;
    public const USE_LIBRARY = true;

    // public const USE_SEARCH = false;

    public $command = [
        'rotate' => [
            'exec'       => null,
            'init'       => null,
            'rotateFile' => null],
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
