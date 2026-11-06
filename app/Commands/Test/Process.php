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
use Mediatag\Commands\Test\HelperCmd\TestCommands;

include_once __DATA_MAPS__ . '/WordMap.php';

class Process extends Mediatag
{
    use MediaExecute;
    use MediaProcess;

    public $VideoList      = [];

    public static $CmdList = [];

    public $videoFiles     = [];

    public $method;

    protected $useFuncs    = ['addMeta'];

    public $displayTable;

    public static $TestObj;

    // public $csvfilename = __DOWNLOAD_DIR__.'/pornhub.com-db.csv';

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {

        parent::boot($input, $output);
        if (Option::isTrue('search')) {
            parent::$SearchArray = parent::$finder->ExecuteSearch();
        }

        self::$TestObj = new TestCommands($input, $output);


        if (Option::isTrue('list')) {
            self::$CmdList = get_class_methods(self::$TestObj);
            Mediatag::$Console->writeln('Available commands:');
            foreach (self::$CmdList as $cmd) {
                Mediatag::$Console->writeln($cmd);
            }

            return true;
        }
        $cmd = Option::getValue('cmd');
        if ($cmd !== null) {
            if (method_exists(self::$TestObj, $cmd)) {
                Mediatag::$Console->writeln($cmd . ' found');
                $this->displayTable = new MediaTable($output);
                $this->method       = $cmd;
            } else {
                Mediatag::$Console->writeln('No method found');
            }
        }
    }

    public function execCmdOption()
    {
        if ($this->method !== null) {
            $fileList = $this->VideoList['file'];
            foreach ($fileList as $key => $file) {
                $this->videoFiles[] = $file['video_file'];
            }
            $cmd      = $this->method;
            self::$TestObj->$cmd();
        }
    }
}
