<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Controller\Rename\Commands;

use Mediatag\Controller\Rename\Traits\Helper;
use Mediatag\Core\MediaCliCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rename', description: 'Rename and format file names')]
final class Rename extends MediaCliCommand
{
    use Helper;

    public static $DEFAULT_CMD = \true;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = [
        'rename' => ['renameVids' => null],
    ];
}
