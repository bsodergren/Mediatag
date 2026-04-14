<?php

namespace Mediatag\Commands\Playlist\Commands\Find;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Compact Command
 *
 * @version 2026-02-10 13:28:48
 */
#[AsCommand(name: 'find', description: 'Description for find Command')]
class FindCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = true;

    public $command = ['find' => ['findObjects' => null]];
}
