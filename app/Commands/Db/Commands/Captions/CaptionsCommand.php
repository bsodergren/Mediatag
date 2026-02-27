<?php

namespace Mediatag\Commands\Db\Commands\Captions;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Captions Command
 *
 * @version 2026-02-08 12:28:50
 */
#[AsCommand(name: 'captions', description: 'Description for Captions Command')]
class CaptionsCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = false;

    public $command = ['captions' => ['captionsMethod' => null]];
}
