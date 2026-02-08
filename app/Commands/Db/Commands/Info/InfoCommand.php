<?php

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Info Command
 *
 * @version 2026-02-08 11:29:18
 */
#[AsCommand(name: 'info', description: 'Description for Info Command')]
class InfoCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = true;

    public $command = ['info' => ['infoMethod' => null]];
}
