<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

// const DESCRIPTION = 'Test Command';
// const NAME        = 'test';

#[AsCommand(name: 'test', description: 'Test Command')]
final class Command extends MediaCommand
{
    // use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = true;

    public static $DEFAULT_CMD = true;

    public $command = [
        'test' => [
            'exec'          => null,
            'execCmdOption' => null,
        ],

    ];
}
