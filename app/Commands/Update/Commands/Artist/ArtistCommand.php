<?php

namespace Mediatag\Commands\Update\Commands\Artist;

/**
 * Command like Metatag writer for video files.
 */

use Mediatag\Commands\Update\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'artist', description: 'Clears metatags on files')]
final class ArtistCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;

    public const USE_SEARCH = true;

    public $command = [
        'artist' => [

            'exec'           => null,
            'updateAristMap' => null,
            // 'exec' => null,
        ],
    ];
}
