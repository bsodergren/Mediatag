<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Test Command';
const NAME        = 'test';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
}
