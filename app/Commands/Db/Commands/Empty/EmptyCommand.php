<?php

namespace Mediatag\Commands\Db\Commands\Empty;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Empty Command
 *
 * @version 2026-02-08 11:40:05
 */
#[AsCommand(name: 'empty', description: 'Description for Empty Command')]
class EmptyCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    public $command = ['empty' => ['execEmpty' => null]];
}
