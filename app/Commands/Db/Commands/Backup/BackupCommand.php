<?php

namespace Mediatag\Commands\Db\Commands\Backup;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Backup Command
 *
 * @version 2026-02-08 12:52:05
 */
#[AsCommand(name: 'backup', description: 'Description for Backup Command')]
class BackupCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['backup' => ['backupMethod' => null]];
}
