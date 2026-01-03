<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'info', description: 'Add files to Database')]
final class InfoCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = true;

    public $command = [
        'info' => [
            'exec' => 'VideoFileInfo',
            // 'exec'     => null,
            // 'execInfo' => null
        ],
    ];
}
