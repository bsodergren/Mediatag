<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\All;

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'all', description: 'Update all DB Info')]
final class AllCommand extends MediaCommand
{
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'all'    => [
            'init'            => null,
            'exec'            => null,
            'execThumb'       => null,
            'execInfo'        => null,
            'execPreview'     => null, ],
    ];
}
