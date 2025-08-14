<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'lang', description: 'add a new translation')]
final class LangCommand extends MediaCommand
{
    public const USE_LIBRARY = false;
    public const SKIP_SEARCH = true;

    public $command = [
        'lang'    => ['langCommand' => null],
    ];
}
