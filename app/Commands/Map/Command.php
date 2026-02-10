<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'map', description: 'Adds DB Mapping for Genre, Keywords and studio')]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
}
