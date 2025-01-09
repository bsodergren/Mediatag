<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update\Commands\Clear;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'clear', description: '')]
final class ClearCommand extends MediaCommand
{
    use MediaExecute;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'clear'    => [
            'exec'      => null,
            'clearMeta' => null,],
    ];
}
