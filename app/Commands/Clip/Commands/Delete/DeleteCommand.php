<?php
namespace Mediatag\Commands\Clip\Commands\Delete;
/**
 * Command like Metatag writer for video files.
 */


use Mediatag\Core\MediaCommand;
use Mediatag\Commands\Clip\Lang;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'delete', description: 'delete Command')]
class DeleteCommand extends MediaCommand
{
    // use Lang;
    public const USE_LIBRARY     = true;
    public $command = [
        'delete'          => [
            'deleteClips' => null,
        ],    ];

}
