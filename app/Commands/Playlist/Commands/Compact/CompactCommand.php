<?php

namespace Mediatag\Commands\Playlist\Commands\Compact;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Compact Command
 *
 * @package Mediatag\Commands\Playlist\Commands\Compact
 * @version 2026-02-10 13:28:48
 */
#[AsCommand(name: 'compact', description: 'Description for Compact Command')]
class CompactCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;
    public const USE_SEARCH = true;

    public $command = ['compact' => ['compactMethod' => null]];
}
