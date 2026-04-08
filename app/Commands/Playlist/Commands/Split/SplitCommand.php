<?php

namespace Mediatag\Commands\Playlist\Commands\Split;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Split Command
 *
 * @version 2026-02-10 13:28:48
 */
#[AsCommand(name: 'split', description: 'Description for Split Command')]
class SplitCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = true;

    public $command = ['split' => ['splitMethod' => null]];
}
