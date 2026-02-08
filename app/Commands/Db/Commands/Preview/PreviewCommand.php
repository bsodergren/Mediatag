<?php

namespace Mediatag\Commands\Db\Commands\Preview;

use Mediatag\Commands\Db\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Preview Command
 *
 * @version 2026-02-08 11:46:00
 */
#[AsCommand(name: 'preview', description: 'Description for Preview Command')]
class PreviewCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = false;

    public $command = ['preview' => ['previewMethod' => null]];
}
