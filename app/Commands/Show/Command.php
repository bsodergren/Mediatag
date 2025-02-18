<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Show files & tags';
const NAME        = 'show';

#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
}
