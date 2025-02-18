<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Captions;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'captions', description: 'ACaptions')]
final class CaptionsCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;
    public $command          = [
        'captions'    => ['execCaptions' => null],
    ];

    public $defaultCommands = [
        //  'init' => null,
        //  'exec' => null,
    ];
}
