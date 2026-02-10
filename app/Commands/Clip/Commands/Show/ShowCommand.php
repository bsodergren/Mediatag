<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Show;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'show', description: 'list available filters')]
final class ShowCommand extends MediaCommand
{
    public const USE_LIBRARY = false;

    public const USE_SEARCH = false;

    public $command = [
        'show' => ['filters' => null],
    ];
}
