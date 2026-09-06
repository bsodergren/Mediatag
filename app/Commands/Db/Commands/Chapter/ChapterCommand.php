<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Chapter;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Info Command
 *
 * @version 2026-02-08 11:29:18
 */
#[AsCommand(name: 'chapter', description: 'Description chapter Info Command')]
class ChapterCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH  = false;

    public $command          = ['chapter' => ['chapterMethod' => null]];
}
