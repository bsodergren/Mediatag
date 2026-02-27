<?php

namespace Mediatag\Commands\Rename\Commands\Fix;

use Mediatag\Commands\Rename\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Fix Command
 *
 * @version 2026-02-08 13:20:33
 */
#[AsCommand(name: 'fix', description: 'Description for Fix Command')]
class FixCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = ['fix' => ['renameVids' => null]];
}
