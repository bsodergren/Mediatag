<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Show files & tags';
const NAME        = 'show';

#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand
{
    use Lang;
    use MediaExecute;

    public const USE_LIBRARY = true;
}
