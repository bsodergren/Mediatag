<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert\Commands\Resize;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'resize', description: 'resize videos')]
final class ResizeCommand extends MediaCommand
{
    public const USE_LIBRARY = false;

    public const SKIP_SEARCH = true;

    public $command = [
        'resize' => [
            'execResize'  => null,
            'ResizeFiles' => null

        ],
    ];

}
