<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Test Command';
const NAME = 'test';
#[AsCommand(name: 'test', description: DESCRIPTION, aliases: ['thumbnail', 'compare', 'download'])]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const SKIP_SEARCH = true;

    public $command = [
        'test'     => [
            'execWord' => null,
            // 'exec'         => null,
            // 'getChanges'   => null,
            // 'writeChanges' => null,
        ],
        'download' => [
            'execDownload' => null,
            //     'doThumbnail' => null,
            //     //     // 'exec' => null,
        ],
        // 'compare'   => [
        //     'exec'    => null,
        //     'compare' => null
        // ],

    ];
}
