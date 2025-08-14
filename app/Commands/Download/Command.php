<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'download', description: 'Download and move new videos to Media Folder')]
final class Command extends MediaCommand
{
    use Lang;
    public const SKIP_SEARCH = true;

    //  public const USE_LIBRARY     = false;
}
