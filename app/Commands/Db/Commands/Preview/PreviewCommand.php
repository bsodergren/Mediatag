<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Preview;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'preview', description: 'Add files to Database')]
final class PreviewCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const SKIP_SEARCH = true;

    public $command = [
        'preview' => [
            // 'init'    => null,
            // 'exec'    => null,
            'execPreview' => null],
    ];
}
