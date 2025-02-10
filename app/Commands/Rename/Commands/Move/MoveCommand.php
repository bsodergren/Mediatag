<?php
namespace Mediatag\Commands\Rename\Commands\Move;

/**
 * Command like Metatag writer for video files.
 */


use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'move', description: 'Move downloaded files from Premium')]
final class MoveCommand extends MediaCommand
{
     
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
    public $command = [
        'move'    => ['moveStudios' => null],
    ];

}
