<?php

namespace Mediatag\Commands\Db\Commands\Remove;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for All Command
 *
 * @version 2026-02-08 11:40:11
 */
#[AsCommand(name: 'remove', description: 'Description for remove Command')]
class RemoveCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['remove' => [
        'init'        => null,
        // 'exec'        => null,
        'removeEntry' => null,    ],
    ];
}
