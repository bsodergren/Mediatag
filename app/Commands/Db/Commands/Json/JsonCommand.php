<?php

namespace Mediatag\Commands\Db\Commands\Json;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Json Command
 *
 * @version 2026-02-08 11:29:18
 */
#[AsCommand(name: 'json', description: 'Description for Json Command')]
class JsonCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    public $command = ['json' => ['JsonExec' => null]];
}
