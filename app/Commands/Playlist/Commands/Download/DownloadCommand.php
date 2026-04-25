<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist\Commands\Download;

use Mediatag\Commands\Playlist\Lang;
use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

const DESCRIPTION = 'download PH Playlist';
const NAME        = 'download';

#[AsCommand(name: 'download', description: 'download PH Playlist')]
final class DownloadCommand extends MediaCommand
{
    use Lang;

    public const USE_LIBRARY = false;

    public const USE_SEARCH = false;

    // public static $SingleCommand = true;
    public static $DEFAULT_CMD = true;

    public $process;

    protected $db;

    public $command = [
        'download' => [
            // 'handler'            => [
            //     'Helper' => 'ShellPathCompletion',
            //     'Option' => 'file',
            //     'Type'   => 'Completion::ALL_TYPES',
            // ],

            'cleanBrkDownloads'  => null,
            'docompactPlaylist'  => null,
            'dodownloadPlaylist' => null,
        ],

    ];
}
