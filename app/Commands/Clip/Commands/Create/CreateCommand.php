<?php
namespace Mediatag\Commands\Clip\Commands\Create;
/**
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Mediatag\Core\Helper\MediaExecute;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create', description: 'Create Command')]
class CreateCommand extends MediaCommand
{

    use MediaExecute;
    public const USE_LIBRARY = true;

    public $command = [
        'create'          => [
            'exec'         => null,
            'getfileList'  => null,
            'createClips'  => null,
        ],    ];
}
