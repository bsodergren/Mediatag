<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Add;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'add', description: 'add Markers to video file')]
final class AddCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'add'    => [
            'exec'         => null,
            'addMarker'    => null, ],
    ];
}
