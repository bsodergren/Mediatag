<?php

namespace Mediatag\Controller\Rename\Commands;

use Mediatag\Controller\Rename\Traits\MoveFuncs;
use Mediatag\Core\MediaCliCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Move Command
 *
 * @version 2026-02-08 13:26:42
 */
#[AsCommand(name: 'move', description: 'Description for Move Command')]
class Move extends MediaCliCommand
{
    use MoveFuncs;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    //  protected function executeAction(): int
    //     {
    //         $this->moveStudios();
    //         $this->prunedirs();
    //             return self::SUCCESS;

    //     }
    public $command = [
        'move' => ['moveStudios' => null,
            'prunedirs'          => null,
        ],
    ];
}
