<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'clip', description: 'clip and format file names')]
final class Command extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        // 'rename'    => ['renameVids' => null],
    ];
}
