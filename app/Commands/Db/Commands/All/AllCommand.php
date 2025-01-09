<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\All;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'all', description: 'Update all DB Info')]
final class AllCommand extends MediaCommand
{
    use MediaExecute;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'all'    => [
            'execThumb'       => null,
            'execInfo'        => null,
            'execPreview'     => null,],
    ];
}
