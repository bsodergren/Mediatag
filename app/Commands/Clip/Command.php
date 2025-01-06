<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\MediaCommand;
use Mediatag\Core\Helper\MediaExecute;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Clip Command';
const NAME        = 'clip';
#[AsCommand(name: NAME, description: DESCRIPTION)]
final class Command extends MediaCommand 
{
    use Lang;
    use MediaExecute;
        public const USE_LIBRARY = true;
}
