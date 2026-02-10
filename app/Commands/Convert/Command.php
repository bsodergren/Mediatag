<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'convert', description: 'Converts metatags on files')]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = false;

    public $command = [
        'convert' => [
            'exec'         => null,
            'ConvertFiles' => null,

        ],
    ];
}
