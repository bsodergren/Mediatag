<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Gallery;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Database\GalleryStorageDB;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Traits\Translate;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function define;
use function dirname;

use const DIRECTORY_SEPARATOR;

class Process extends Mediatag
{
    use Helper;
    use Lang;
    use MediaExecute;
    use MediaProcess;
    use Translate;
    public $db_array = [];

    public $file_array = [];

    public $read;

    public $meta;

    public $OutputText = [];

    public $New_Array = [];

    public $Deleted_Array = [];

    public $Changed_Array = [];

    public $duration;
    public $VideoList = [];

    public $defaultCommands = [
        'init' => null,
        'exec' => null,
    ];

    public $commandList = [
        // 'info'         => ['execInfo' => null, 'checkClean' => null],
        // 'update'       => ['execUpdate' => 'default'],
        'empty'        => ['execEmpty' => 'default'],
    ];

    private $count;
    public object $DbMap;
    private $thumb;

    private $vinfo;

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        // utminfo(func_get_args());
        define('SKIP_SEARCH', true);
        parent::boot($input, $output);
        parent::$dbconn = new GalleryStorageDB($input, $output);
    }

    public function exec($option = null)
    {
        // utminfo(func_get_args());
        $this->getFileArray();
        $this->removeDBEntry();
        $this->changeDBEntry();

        $this->addDBEntry();

        //         $this->VideoList     = parent::getVideoArray();
        //   utmdd($this->VideoList);
    }

    public function init()
    {
        // utminfo(func_get_args());
        $path = getcwd();

        $finder          = new MediaFinder();
        $this->VideoList = $finder->Search($path, '/\.jpg|\.png|\.gif$/i');

        parent::$SearchArray = $this->VideoList;
        $file_array          = parent::$SearchArray;
        $this->DbMap         = new DbMap();

        foreach ($file_array as $k => $file) {
            $key = File::getVideoKey($file);

            if (array_key_exists($key, $this->file_array)) {
                $movedFile = str_replace('/'.__LIBRARY__, '/Dupes/'.__LIBRARY__, $file);
                $dupePath  = dirname($movedFile);
                $filename  = basename($file);

                $dupePath = nFileSystem::normalizePath($dupePath);
                if (!is_dir($dupePath)) {
                    //     if (!Option::isTrue('test')) {
                    nFileSystem::createDir($dupePath, 0755);
                    //     }
                }
                Mediatag::$output->writeln($file.' is dup');
                (new SfSystem())->rename($file, $dupePath.DIRECTORY_SEPARATOR.$filename, true);
                continue;
            }
            $this->file_array[$key] = $file;
        }

        parent::$dbconn->file_array = $this->file_array;
        $this->db_array             = parent::$dbconn->getDbFileList();

        return $this;
    }
}
