<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Export;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'export', description: 'Export video metadata')]
class ExportCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = false;

    public $command = [
        'export' => [
            'exportMetaData' => null,
        ],
    ];
}
