<?php

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Thumbnail Command
 *
 * @version 2026-02-08 11:40:00
 */
#[AsCommand(name: 'thumbnail', description: 'Description for Thumbnail Command')]
class ThumbnailCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['thumbnail' => ['thumbnailMethod' => null]];
}
