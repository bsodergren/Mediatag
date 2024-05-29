<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Adds DB Mapping for Genre, Keywords and studio';
const NAME        = 'map';
#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
}
