<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

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
// use Mediatag\Commands\Db\Commands\Info\Helper as InfoHelper;
// use Mediatag\Commands\Db\Commands\Captions\Helper as CapHelper;
// use Mediatag\Commands\Db\Commands\EmptyDB\Helper as EmptyHelper;
// use Mediatag\Commands\Db\Commands\Preview\Helper as PreviewHelper;
// use Mediatag\Commands\Db\Commands\Thumbnail\Helper as ThumbHelper;
// use Mediatag\Commands\Db\Commands\Backup\Helper as BackHelper;
use UTM\Utilities\Option;

use function array_key_exists;
use function dirname;

use const DIRECTORY_SEPARATOR;

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

    public $file_array   = [];
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

        'json'         => [
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
        if($this->Search_Array ===  null){
            $this->Search_Array = parent::$finder->Search(null,'/\.mp4$/i', null, false);
        }
        // $this->DbMap = new DbMap();
        $this->allDbFiles = parent::$dbconn->getAllDbFiles();
        if(count($this->Search_Array) > 0 ) {
            foreach ($this->Search_Array as $k => $file) {
                $key = File::getVideoKey($file);

                if (array_key_exists($key, $this->allDbFiles)) {
                    $existing_file = $this->allDbFiles[$key];

                    //    utmdd([$file,$key,$this->allDbFiles[$key]]);
                    if ($existing_file != $file) {
                        utmdump([$existing_file, $file]);

                        [$keep,$move] = VideoFileInfo::compareDupes($existing_file, $file);

                        // utmdd(['move'=>$move, $file, $this->file_array[$key]]);
                        Mediatag::$Console->writeln('existi file '.$existing_file.'');
                        Mediatag::$Console->writeln('Keepin file '.$keep.'');
                        if (file_exists($move)) {
                            Mediatag::$Console->writeln('Moving file '.$move.'');
                            $movedFile = str_replace('/'.__LIBRARY__, '/Dupes/'.__LIBRARY__, $move);
                            $dupePath  = dirname($movedFile);
                            $filename  = basename($file);

                            $dupePath = nFileSystem::normalizePath($dupePath);
                            if (!is_dir($dupePath)) {
                                //     if (!Option::isTrue('test')) {
                                nFileSystem::createDir($dupePath, 0755);
                                //     }
                            }
                            Mediatag::$Console->writeln('to '.$dupePath.DIRECTORY_SEPARATOR.$filename.'');
                            (new SfSystem())->rename($move, $dupePath.DIRECTORY_SEPARATOR.$filename, true);
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
