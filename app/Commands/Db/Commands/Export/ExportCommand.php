<?php

namespace Mediatag\Commands\Db\Commands\Export;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Export Command
 *
 * @package Mediatag\Commands\Db\Commands\Export
 * @version 2026-02-08 13:10:53
 */
#[AsCommand(name: 'export', description: 'Description for Export Command')]
class ExportCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = ['export' => ['exportMethod' => null]];
}
