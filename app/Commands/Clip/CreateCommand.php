<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create', description: 'Create Command')]
class CreateCommand extends MediaCommand
{
    use Lang;
    public const USE_LIBRARY = true;

    public $command = [
        'create'          => [
            'exec'         => null,
            'getfileList'  => null,
            'createClips'  => null,
        ],    ];
}
