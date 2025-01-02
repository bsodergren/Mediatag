<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Adds DB Mapping for Genre, Keywords and studio';
const NAME        = 'map';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;

    public const USE_LIBRARY = true;
}
