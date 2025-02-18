<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Download;

use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Traits\Callables\Callables;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

class Process extends Mediatag
{
    use Callables;
    use Helper;
    use MediaProcess;
    /**
     * file_array.
     *
     * @var array
     */
    public $file_array = [];

    public $newFiles = [];

    public $barSection;

    public $textSection;

    public $commandList = [
        'json'    => [
            'jSonCache' => null,
        ],
        'convert' => [
            'convertVideos' => null,
        ],
    ];

    public $defaultCommands = [
        'moveDownloads' => null,
    ];

    private $filesToRemove = [];

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        // utminfo(func_get_args());

        if (__PLEX_DOWNLOAD__ != getcwd()) {
            chdir(__PLEX_DOWNLOAD__);
        }

        if (Option::istrue('convert')) {
            parent::boot($input, $output, ['SKIP_SEARCH' => true]);
        } else {
            parent::boot($input, $output);
        }
    }
}
