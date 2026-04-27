<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Commands\Test\HelperCmd\Helper;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaTable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Utilities\Option;

include_once __DATA_MAPS__ . '/WordMap.php';

class Process extends Mediatag
{
    use Helper;
    use MediaExecute;
    use MediaProcess;

    public $VideoList = [];

    public static $CmdList = [];

    public $videoFiles = [];

    public $method = null;

    protected $useFuncs = ['addMeta'];

    public $displayTable;

    // public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);
        $cmd = Option::getValue('cmd');
        if (\method_exists($this, $cmd)) {
            Mediatag::$Console->writeln($cmd . ' found');
            $this->displayTable = new MediaTable($output);
            $this->method       = $cmd;
        } else {
            Mediatag::$Console->writeln('No method found');
        }
    }

    public function execCmdOption()
    {
        if (! is_null($this->method)) {
            $fileList = $this->VideoList['file'];
            foreach ($fileList as $key => $file) {
                $this->videoFiles[] = $file['video_file'];
            }
            $cmd = $this->method;
            $this->$cmd();
        }
    }
}
