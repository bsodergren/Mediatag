<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Rename;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rename', description: 'Rename and format file names')]
final class Command extends MediaCommand
{
    use MediaExecute;

    public const USE_LIBRARY = true;
}
