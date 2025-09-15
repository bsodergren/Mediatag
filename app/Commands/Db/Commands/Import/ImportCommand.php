<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Import;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'import', description: 'backup the db at the directory')]
final class ImportCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;

    public $command = [
        'import'    => ['execImport' => null],
    ];
}
