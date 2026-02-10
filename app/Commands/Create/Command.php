<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'new', description: 'Create a new Command')]
class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = false;

    public $command = [
        'new' => ['createCommand' => null],
    ];
}
