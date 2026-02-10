<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Sort;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Sort files & tags';
const NAME        = 'sort';

#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    public $command = [
        'sort' => [
            'exec'  => null,
            'print' => null,
        ],
    ];
}
