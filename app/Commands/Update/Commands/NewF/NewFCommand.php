<?php

namespace Mediatag\Commands\Update\Commands\NewF;

/**
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

//

#[AsCommand(name: 'new', description: 'Add files to Database')]
final class NewFCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = false;
    // public const new = true;

    public $command = [
        'new' => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
            'dbUpdate'     => null,
        ],
    ];
}
