<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'info', description: 'Add files to Database')]
final class InfoCommand extends MediaCommand
{
    use MediaExecute;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;

}
