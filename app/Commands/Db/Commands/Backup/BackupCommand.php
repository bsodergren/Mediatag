<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Backup;

/*
 * Command like Metatag writer for video files.
 */

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'backup', description: 'backup the db at the directory')]
final class BackupCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'backup'    => ['execBackup' => null],
    ];
}
