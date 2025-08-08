<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Subtitles;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'subtitle', description: 'Add files to Database')]
final class SubtitleCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
    public $command          = [
        'subtitle'    => [
            // 'init' => null,
            // 'exec' => null,
            'execSubtitles' => null],
    ];
}
