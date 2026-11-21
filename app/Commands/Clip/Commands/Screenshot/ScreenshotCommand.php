<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Screenshot;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'Screenshot', description: 'Screenshot Screenshots to video file')]
final class ScreenshotCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const USE_SEARCH  = true;

    public $command          = [
        'Screenshot' => [
            'exec'               => null,
            'getScreenshotList'     => null,
            'createScreenshotFile'  => null, ],
    ];
}
