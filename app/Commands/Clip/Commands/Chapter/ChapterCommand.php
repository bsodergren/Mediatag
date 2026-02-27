<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Chapter;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'Chapter', description: 'Chapter Markers to video file')]
final class ChapterCommand extends MediaCommand
{
    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = [
        'Chapter' => [
            'exec'              => null,
            'getMarkerList'     => null,
            'createChapterFile' => null, ],
    ];
}
