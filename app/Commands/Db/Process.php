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
    public $Search_Array = [];

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
        $this->Search_Array = parent::$SearchArray;
    }

    public function init()
    {
        // utminfo(func_get_args());

        // $this->DbMap = new DbMap();

        foreach ($this->Search_Array as $k => $file) {
            $key = File::getVideoKey($file);

            if (array_key_exists($key, $this->file_array)) {
                // utmdd([$file,$key,$this->file_array[$key]]);

                [$keep,$move] = VideoFileInfo::compareDupes($this->file_array[$key], $file);
                // utmdump(['move'=>$move, $file, $this->file_array[$key]]);
                Mediatag::$Console->writeln('Keeping file '.$keep.'');
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
                unset($this->file_array[$key]);
                $this->file_array[$key] = $keep;
                continue;
            }
            $this->file_array[$key] = $file;
        }
        parent::$dbconn->file_array = $this->file_array;
        // utmdd("fds");
        $this->db_array = parent::$dbconn->getDbFileList();

        // utmdd( $this->db_array  );
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
