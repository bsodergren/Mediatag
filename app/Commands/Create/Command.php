<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create', description: 'Create a new Command')]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;
    public const SKIP_SEARCH = true;

    public $command = [
        'create'    => ['createCommand' => null],
    ];
}
