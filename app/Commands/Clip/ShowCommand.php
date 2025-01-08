<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'show', description: 'list available filters')]
final class ShowCommand extends MediaCommand
{
    use MediaExecute;
    public const USE_LIBRARY = false;
    public const SKIP_SEARCH = true;

    public $command = [
        'show'    => ['filters' => null,],
    ];
}
