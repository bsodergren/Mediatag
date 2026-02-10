<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create\Commands\Add;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'add', description: 'add a new Command')]
final class AddCommand extends MediaCommand
{
    public const USE_LIBRARY = false;

    public const USE_SEARCH = false;

    public $command = [
        'add' => ['addCommand' => null],
    ];
}
