<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update\Commands\Clear;

use Mediatag\Commands\Update\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'clear', description: 'Clears metatags on files')]
final class ClearCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = false;

    public $command = [
        'clear' => [

            'exec'      => null,
            'clearMeta' => null,
            // 'exec' => null,
        ],
    ];
}
