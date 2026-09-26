<?php

namespace Mediatag\Commands\Test\Commands\Export;

use Mediatag\Commands\Test\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Export Command
 *
 * @package Mediatag\Commands\Test\Commands\Export
 * @version 2026-09-26 10:49:19
 */
#[AsCommand(name: 'export', description: 'Description for Export Command')]
class ExportCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;
    public const USE_SEARCH = false;

    public $command = ['export' => ['exportJson' => null]];
}
