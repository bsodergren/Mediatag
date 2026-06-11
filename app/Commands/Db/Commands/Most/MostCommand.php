<?php

namespace Mediatag\Commands\Db\Commands\Most;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for All Command
 *
 * @version 2026-02-08 11:40:11
 */
#[AsCommand(name: 'most', description: 'Description for Most Command')]
class MostCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['most' => [
        'init'      => null,
        'exec'      => null,
        'execInfo'  => null,
        'execThumb' => null,
        'execJson'  => null,
    ],
    ];
}
