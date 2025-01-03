<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ShowDisplay;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

include_once __DATA_MAPS__.'/WordMap.php';

class Process extends Mediatag
{
    use Helper;
    use Lang;
    use MediaProcess;

    public $VideoList = [];

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList = [
        'convert'       => [
            'exec'          => null,
            'getfileList'   => null,
            'getClips'      => null,
        ],
        'clip'          => [
            'exec'        => null,
            'getfileList' => null,
            'createClip'  => null,
        ],
        'delete'        => [
            'deleteClips' => null,
        ],
    ];

    public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        // utminfo(func_get_args());
        if (!Option::isTrue('clip')) {
            \define('SKIP_SEARCH', true);
        }

        parent::boot($input, $output);
        $this->setupFormat();
        $this->setupDb();
        //  utmdd(Command::$logger);

        // parent::$Display              = new ShowDisplay($output);
    }
}
