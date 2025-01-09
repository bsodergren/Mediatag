<?php

namespace Mediatag\Commands\Db\Commands\Empty;
/**
 * Command like Metatag writer for video files.
 */


use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'empty', description: 'Empty the db at the directory')]
final class EmptyCommand extends MediaCommand
{
    use MediaExecute;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;

    public $command = [
        'empty'    => ['execEmpty' => null,],
    ];
}
