<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'gallery', description: 'gallery stuff')]
class Command extends MediaCommand
{
    public const USE_LIBRARY = true;
}
