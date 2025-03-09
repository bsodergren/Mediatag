<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update\Commands\Update;

use Mediatag\Core\MediaCommand;
use Mediatag\Commands\Update\Lang;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'update', description: 'Updates metatags on files')]
final class UpdateCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
    public $command          = [
        'update'    => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
        ],
       
    ];
}
