<?php
namespace Mediatag\Commands\Rename\Commands\Fix;
/**
 * Command like Metatag writer for video files.
 */


use Mediatag\Core\Mediatag;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'fix', description: 'Rename and format file names')]
final class FixCommand extends MediaCommand
{

    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;



    public $command = [
        'fix'    => [
            'renameVids'       => null,
            // 'execInfo'        => null,
            // 'execPreview'     => null,
            ],
    ];

    // public $command = [

        
        // 'fix'    => ['renameVids' => null],
    // ];

}
