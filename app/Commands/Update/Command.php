<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'update', description: 'Updates metatags on files', aliases: ['clear'])]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public $command          = [
        'update'    => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
        ],
        'clear'    => [
            'exec' => null,
            'clearMeta' => null,
            // 'exec' => null,
        ],
    ];
}
