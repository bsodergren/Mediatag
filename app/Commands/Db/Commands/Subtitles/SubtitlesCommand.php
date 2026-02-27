<?php

namespace Mediatag\Commands\Db\Commands\Subtitles;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Subtitles Command
 *
 * @version 2026-02-08 13:15:50
 */
#[AsCommand(name: 'subtitles', description: 'Description for Subtitles Command')]
class SubtitlesCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['subtitles' => ['subtitlesMethod' => null]];
}
