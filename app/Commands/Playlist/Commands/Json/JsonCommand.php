<?php

namespace Mediatag\Commands\Playlist\Commands\Json;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Json Command
 *
 * @version 2026-02-10 13:28:48
 */
#[AsCommand(name: 'json', description: 'Description for Json Command')]
class JsonCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = true;

    public $command = ['json' => ['JsonMethod' => null]];
}
