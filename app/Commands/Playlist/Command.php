<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

const DESCRIPTION = 'download PH Playlist';
const NAME        = 'playlist';

use Mediatag\Core\MediaCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'playlist', description: 'download PH Playlist', aliases: ['pl', 'compact', 'download', 'json'])]
final class Command extends MediaCommand
{
    use Lang;

    public const CMD_NAME = NAME;

    public const CMD_DESCRIPTION = DESCRIPTION;

    public const USE_LIBRARY = false;

    public const SKIP_SEARCH = true;
    // public static $SingleCommand = true;

    public $process;

    protected $db;

    public $command = [
        'pl'       => [
            'handler'            => [
                'Helper' => 'ShellPathCompletion',
                'Option' => 'file',
                'Type'   => 'Completion::ALL_TYPES',
            ],

            'cleanBrkDownloads'  => null,

            'docompactPlaylist'  => null,
            'dodownloadPlaylist' => null,
        ],
        'download' => [
            'handler'               => [
                'Helper' => 'ShellPathCompletion',
                'Option' => 'file',
                'Type'   => 'Completion::ALL_TYPES',
            ],
            'cleanBrkDownloads'     => null,
            // 'docompactPlaylist'           => null,
            'dodownloadPlaylistURL' => null,
        ],
        // 'missing'           => [
        //     // 'exec'        => null,
        //     'missing' => null,
        // ],
        'find'     => [
            'find' => null,
            // 'default' => null,
        ],
        // 'cleanBrkDownloads' => [
        //     'cleanBrkDownloads' => null,
        // ],
        'compact'  => [
            'docompactPlaylist' => true,
        ],
        // 'clean'             => [
        //     'clean' => null,
        // ],
        // 'max'               => [
        //     'trimPlaylist' => null,
        //     'default'      => null,
        // ],
        'json'     => [
            'cleanjSon' => null,
        ],
        // 'watchlater'        => [
        //     'youtubeWatchPlaylist' => null,
        //     'compact'              => null,
        // ],
        // 'premium'           => [
        //     // 'exec'        => null,
        //     'premium' => null,
        //     'compact' => null,
        // ],
        // 'split'             => [
        //     // 'exec'        => null,
        //     'splitPlaylist' => null,
        // ],
    ];
}
