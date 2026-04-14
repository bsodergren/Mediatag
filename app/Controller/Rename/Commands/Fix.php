<?php

namespace Mediatag\Controller\Rename\Commands;

use Mediatag\Core\MediaCliCommand;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Description for Fix Command
 *
 * @version 2026-02-08 13:20:33
 */
#[AsCommand(name: 'fix', description: 'Description for Fix Command')]
class Fix extends MediaCliCommand
{
    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    protected function executeAction(): int
    {
        return self::SUCCESS;
    }
}
