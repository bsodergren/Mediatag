<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'thumbnail', description: 'Add files to Database')]
final class ThumbnailCommand extends MediaCommand
{
     
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;
    public $command = [
        'thumbnail'    => ['execThumb' => null],
    ];
}
