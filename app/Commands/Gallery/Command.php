<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Add files to Database';
const NAME        = 'Gallery';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;
    public const USE_LIBRARY = true;
}
