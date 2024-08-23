<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
// use Nette\Utils\FileSystem as NetteFile;
use Mediatag\Traits\Callables;
use UTM\Utilities\Option;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// use Symfony\Component\Filesystem\Filesystem;

class Process extends Mediatag
{
    use Callables;
    use Helper;

    public const ARCHIVE            = __PLEX_PL_DIR__ . '/ids/archive.txt';

    public const IGNORED            = __PLEX_PL_DIR__ . '/ids/ignored_ids.txt';

    public const DISABLED           = __PLEX_PL_DIR__ . '/ids/disabled.txt';

    public const MODELHUB           = __PLEX_PL_DIR__ . '/ids/modelhub.txt';

    public const ERRORIDS           = __PLEX_PL_DIR__ . '/ids/error.txt';

    public const NOTFOUND           = __PLEX_PL_DIR__ . '/ids/notfound.txt';

    public const FILELIST           = __PLEX_PL_DIR__ . '/lists/filelist.txt';

    public const DOWNLOADED         = __PLEX_PL_DIR__ . '/lists/downloaded.txt';

    public const FILEMAP            = __PLEX_PL_DIR__ . '/lists/all_files_list.txt';

    public const PLAYLIST           = __PLEX_PL_DIR__ . '/all_playlist.txt';

    public const JSONPLAYLIST       = __PLEX_PL_DIR__ . '/json_playlist.txt';

    public const ERRORPLAYLIST      = __PLEX_PL_DIR__ . '/error_playlist.txt';

    public const MISSING_PLAYLIST   = __PLEX_PL_DIR__ . '/missing_playlist.txt';

    public $defaultCommands         = [
        'cleanBrkDownloads' => null,
        'compact'           => null,
        'download'          => null,
    ];

    public $commandList             = [
        'missing'           => [
            // 'exec'        => null,
            'missing' => null,
        ],
        'find'              => [
            'find' => null,
            // 'default' => null,
        ],
        'cleanBrkDownloads' => [
            'cleanBrkDownloads' => null,
        ],
        'compact'           => [
            'compact' => null,
        ],
        'clean'             => [
            'clean' => null,
        ],
        'max'               => [
            'trimPlaylist' => null,
            'default'      => null,
        ],
        'json'              => [
            'cleanjSon' => null,
        ],
        'watchlater'        => [
            'youtubeWatchPlaylist' => null,
            'compact'              => null,
        ],
    ];

    public static $current_key      = false;

    public static $trimmedPlaylist  = false;

    public static $originalPlaylist = false;

    public $playlist ;

    public $OrigPlaylist;

    public $idList                  = [];

    public $premiumIds              = [];

    public $json_Array;

    public $ids;

    public $premium;

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        utminfo();

        define('SKIP_SEARCH', true);
        parent::boot($input, $output);
        if ($file === null) {
            $file = Option::getValue('playlist');
        }
        $this->playlist = $file;


        if (!is_dir(__PLEX_PL_TMP_DIR__)) {
            Filesystem::createDir(__PLEX_PL_TMP_DIR__, 0755);
        }
    }

    // public function __construct(InputInterface $input, OutputInterface $output)
    // {
    //     parent::boot($input, $output);
    //     $file = Option::getValue('playlist');
    //  // utmdd(["f",$file]);
    //     $this->playlist = $file;

    //     if (! is_dir(__PLEX_PL_TMP_DIR__)) {
    //         Filesystem::createDir(__PLEX_PL_TMP_DIR__, 0755);
    //     }
    // }

    public function __call($m, $a)
    {
        utminfo();

        return null;
    }

    // end print()
}
