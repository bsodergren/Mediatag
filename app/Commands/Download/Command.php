<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

const DESCRIPTION = 'Download and move new videos to Media Folder';
const NAME = 'download';

#[AsCommand(name: NAME, description: DESCRIPTION)]
class Command extends MediaCommand
{
    use Lang;

    //  public const USE_LIBRARY     = false;
}
