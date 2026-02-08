<?php

namespace Mediatag\Commands\Db\Commands\All;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for All Command
 *
 * @version 2026-02-08 11:40:11
 */
#[AsCommand(name: 'all', description: 'Description for All Command')]
class AllCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = false;

    public $command = ['all' => [
        'init'        => null,
        'exec'        => null,
        'execThumb'   => null,
        'execInfo'    => null,
        'execPreview' => null,
    ],
    ];
}
