<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

// const DESCRIPTION = 'Test Command';
// const NAME        = 'test';

#[AsCommand(name: 'test', description: 'Test Command', aliases: ['thumbnail', 'compare', 'download', 'search'])]
final class Command extends MediaCommand
{
    // use Lang;

    public const USE_LIBRARY = false;

    public const SKIP_SEARCH = true;

    public $command = [
        'test' => [
            'exec' => null,
            // 'exec'         => null,
            // 'getChanges'   => null,
            // 'writeChanges' => null,
        ],
        // 'download' => [
        //     'execDownload' => null,
        //     //     'doThumbnail' => null,
        //     //     //     // 'exec' => null,
        // ],
        // 'search'   => [
        //     'searchJson' => null,
        // ],

    ];
}
