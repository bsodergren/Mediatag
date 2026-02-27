<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update\Commands\Update;

use Mediatag\Commands\Update\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'update', description: 'Updates metatags on files')]
final class UpdateCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = [
        'update' => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
        ],
    ];
}
