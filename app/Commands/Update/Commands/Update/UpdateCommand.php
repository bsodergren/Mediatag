<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Update\Commands\Update;

use Mediatag\Commands\Update\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'update', description: 'Updates metatags on files')]
final class UpdateCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = false;
    public $command          = [
        'update'    => [
            'exec'         => null,
            'getChanges'   => null,
            'writeChanges' => null,
        ],
    ];

    public static function ArgumentClosure($input, $command)
    {
        utmdump(['UpdateCommand', $command]);

        // the value the user already typed, e.g. when typing "app:greet Fa" before
        // pressing Tab, this will contain "Fa"
        $currentValue = $input->getCompletionValue();

        // get the list of username names from somewhere (e.g. the database)
        // you may use $currentValue to filter down the names
        $availableUsernames = ['Single', 'MMF', 'MFF'];

        // then suggested the usernames as values
        return $availableUsernames;
    }
}
