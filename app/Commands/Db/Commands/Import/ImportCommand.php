<?php

namespace Mediatag\Commands\Db\Commands\Import;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Import Command
 *
 * @version 2026-02-08 11:40:18
 */
#[AsCommand(name: 'import', description: 'Description for Import Command')]
class ImportCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    public $command = ['import' => ['importMethod' => null]];
}
