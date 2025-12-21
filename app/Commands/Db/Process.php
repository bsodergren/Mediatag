<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use const DIRECTORY_SEPARATOR;

use Mediatag\Commands\Db\Commands\Subtitles\Helper as SubHelper;
use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Helper\MediaProcess;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Traits\Translate;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function dirname;

class Process extends Mediatag
{
    // use CapHelper;s
    // use EmptyHelper;
    // use BackHelper;
    use Helper;

    // use InfoHelper;
    use Lang;
    use MediaExecute;
    use MediaProcess;

    // use PreviewHelper;
    use SubHelper;
    use Translate;

    protected $useFuncs = ['addMeta'];

    public $db_array = [];

    public $file_array = [];

    public $Search_Array = null;

    public $read;

    public $meta;

    public $OutputText = [];

    public $New_Array = [];

    public $Deleted_Array = [];

    public $Changed_Array = [];

    public $defaultCommands = [
        // 'init' => null,
        // 'exec' => null,
    ];

    public $commandList = [
        // 'markers'      => [
        // 'init'        => null,
        // 'exec'        => null,
        // 'execMarkers' => null],

        // 'update'       => [
        //     // 'init'       => null,
        //     // 'exec'       => null,
        //     'execUpdate' => 'default'],

        'json' => [
            // 'init'    => null,
            // 'exec'    => null,
            'getJson' => null],
    ];

    private $count;

    public object $DbMap;

    private $thumb;

    private $vinfo;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::boot($input, $output);

        //
        //parent::$SearchArray;
    }

    public function init()
    {
        // utminfo(func_get_args());
        if ($this->Search_Array === null) {
            $this->Search_Array = parent::$finder->Search(null, '/\.mp4$/i', null, false);
        }
        // $this->DbMap = new DbMap();
        $this->allDbFiles = parent::$dbconn->getAllDbFiles();
        // utmdd($this->allDbFiles,$this->Search_Array);

        if (count($this->Search_Array) > 0) {
            foreach ($this->Search_Array as $k => $file) {
                $key = File::getVideoKey($file);

                if (array_key_exists($key, $this->allDbFiles)) {
                    $existing_file = $this->allDbFiles[$key];

                       utmdump([$file,$key,$this->allDbFiles[$key]]);
                    if ($existing_file != $file) {
                        utmdump([$existing_file, $file]);

                        [$keep,$move] = VideoFileInfo::compareDupes($existing_file, $file);

                        utmdump(['move'=>$move,'keep'=>$keep]);
                        // Mediatag::$Console->writeln('existi file ' . $existing_file . '');
                        // Mediatag::$Console->writeln('Keepin file ' . $keep . '');
                        if (file_exists($move)) {
                            Mediatag::$Console->write('Moving file ' . $move . ' ' );
                            $movedFile = str_replace('/' . __LIBRARY__, '/Dupes/' . __LIBRARY__, $move);
                            $dupePath  = dirname($movedFile);
                            $filename  = basename($file);

                            $dupePath = nFileSystem::normalizePath($dupePath);
                            if (! is_dir($dupePath)) {
                                //     if (!Option::isTrue('test')) {
                                nFileSystem::createDir($dupePath, 0755);
                                //     }
                            }
                            Mediatag::$Console->writeln('to dupe folder');
                             (new SfSystem)->rename($move, $dupePath . DIRECTORY_SEPARATOR . $filename, true);
                        }

                        unset($this->file_array[$key]);
                        $this->file_array[$key] = $keep;

                        continue;
                    }
                }
                $this->file_array[$key] = $file;
            }
        }
        parent::$dbconn->file_array = $this->file_array;

        $this->db_array = parent::$dbconn->getDbFileList();
// utmdd($this->db_array,$this->file_array);
        return $this;
    }

    public function exec($option = null)
    {
        // utminfo(func_get_args());
        $this->getFileArray();

        $this->removeDBEntry();
        $this->changeDBEntry();

        $this->addDBEntry();

        //  $this->execUpdate();
    }
}
