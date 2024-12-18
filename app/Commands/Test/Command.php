<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCommand;
use Mediatag\Services\MessageGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Test Command';
const NAME        = 'test';
#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;
    public const USE_LIBRARY     = true;

 
}
