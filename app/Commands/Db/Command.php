<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'db', description: 'Add files to Database')]
final class Command extends MediaCommand
{
    public const USE_LIBRARY = true;
    public $command          = [
        'db'    => [
            'init' => null,
            'exec' => null,
        ],
    ];
}
