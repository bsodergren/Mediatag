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

#[AsCommand(name: 'playlist', description: 'download PH Playlist', aliases: ['pl', 'compact', 'download'])]
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
            'cleanBrkDownloads'  => null,
            'docompactPlaylist'  => null,
            'dodownloadPlaylist' => null,
        ],
        'download' => [
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
            'docompactPlaylist' => null,
        ],
        // 'clean'             => [
        //     'clean' => null,
        // ],
        // 'max'               => [
        //     'trimPlaylist' => null,
        //     'default'      => null,
        // ],
        // 'json'              => [
        //     'cleanjSon' => null,
        // ],
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

    // public function handleSignal(int $signal): void
    // {
    //     // utminfo(func_get_args());

    //     if (\SIGINT === $signal) {
    //         echo \PHP_EOL;
    //         echo 'Exiting, cleaning up';
    //         echo \PHP_EOL;
    //         Process::Cleanup();

    //         exit;
    //     }
    // }

    /*
     * Method execute.
     *
     * @param InputInterface  $input  [explicite description]
     * @param OutputInterface $output [explicite description]
     */
    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     // utminfo(func_get_args());

    //     parent::execute($input, $output);

    //     return SymCommand::SUCCESS;
    // }
}
