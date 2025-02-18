<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Add files to Database';
const NAME        = 'Gallery';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
}
