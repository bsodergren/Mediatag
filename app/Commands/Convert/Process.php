<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Convert;

use FFMpeg\FFMpeg;
use Mediatag\Core\Mediatag;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Modules\Filesystem\MediaFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use MediaExecute;
    use MediaProcess;

    /**
     * meta.
     */

    public $ffmpeg;

    public $commandList = [
        // 'empty'     => [
        //     'exec'      => null,
        //     'clearMeta' => null,
        // ],
        // 'download'  => [
        //     'exec'         => null,
        //     'download'     => null,
        //     'writeChanges' => true,
        // ],
        // 'clear'     => [
        //     'exec'      => null,
        //     'clearMeta' => null, ],
        // 'list'      => [
        //     'exec'        => null,
        //     'getChanges'  => null,
        //     //  'saveChanges' => 'isset',
        // ],
    ];

    public $defaultCommands = [
        'exec' => null,

    ];

    protected $useFuncs = [];

    protected $json_file;

    /**
     * __construct.
     *
     * @param  mixed  $input
     * @param  mixed  $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {

        parent::boot($input, $output);

    }
    public function exec()
    {
        MediaFinder::$depth = 1;
        $this->file_array   = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, "*.mov");
        $this->ffmpeg       = FFMpeg::create(array(
            'timeout'        => 3600, // The timeout for the underlying process
            'ffmpeg.threads' => 12,   // The number of threads that FFMpeg should use
        ), Mediatag::$log);

    }
}
