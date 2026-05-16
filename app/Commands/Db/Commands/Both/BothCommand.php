<?php

namespace Mediatag\Commands\Db\Commands\Both;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for All Command
 *
 * @version 2026-02-08 11:40:11
 */
#[AsCommand(name: 'both', description: 'Description for Both Command')]
class BothCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['both' => [
        'init'      => null,
        'exec'      => null,
        'execThumb' => null,
        'execInfo'  => null,    ],
    ];
}
