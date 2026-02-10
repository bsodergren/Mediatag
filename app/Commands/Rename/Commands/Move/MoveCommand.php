<?php

namespace Mediatag\Commands\Rename\Commands\Move;

use Mediatag\Commands\Rename\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Move Command
 *
 * @package Mediatag\Commands\Rename\Commands\Move
 * @version 2026-02-08 13:26:42
 */
#[AsCommand(name: 'move', description: 'Description for Move Command')]
class MoveCommand extends MediaCommand
{
    use Lang;

   public const USE_LIBRARY = true;

    public const USE_SEARCH=true;

    public $command = [
        'move' => ['moveStudios' => null,
            'prunedirs'          => null,
        ],
    ];
}
