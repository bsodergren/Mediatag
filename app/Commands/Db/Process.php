<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Traits\ffmpeg;
use Mediatag\Traits\Translate;
use Nette\Utils\FileSystem as nFileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem as SfSystem;
use UTM\Utilities\Option;

class Process extends Mediatag
{
    use ffmpeg;
    use Helper;
    use Lang;
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

    public $defaultCommands = [
        'init' => null,
        'exec' => null,
    ];

    public $commandList = [
        'all'          => [
            'execThumb'       => null,
            'execDuration'    => null,
            'execInfo'        => null,
            'execPreview'     => null,
        ],
        'thumbnail'    => ['execThumb' => null, 'checkClean' => null],
        'markers'      => ['execMarkers' => null],
        'videopreview' => ['execPreview' => null, 'checkClean' => null],
        'duration'     => ['execDuration' => null, 'checkClean' => null],
        'info'         => ['execInfo' => null, 'checkClean' => null],
        'update'       => ['execUpdate' => 'default'],
        'empty'        => ['execEmpty' => 'default'],
        'json'         => ['getJson' => null],
    ];

    private $count;
    public object $DbMap;
    private $thumb;

    private $vinfo;

    public function __construct(InputInterface $input, OutputInterface $output, $file = null)
    {
        // utminfo(func_get_args());

        if (Option::istrue('thumbnail') || Option::istrue('duration') || Option::istrue('info') || Option::istrue('videopreview')) {
            parent::boot($input, $output, ['SKIP_SEARCH' => true]);
        } else {
            parent::boot($input, $output);

            if (Option::istrue('all')) {
                $this->init();
                $this->exec();
            }
        }
    }

    public function init()
    {
        // utminfo(func_get_args());

        $file_array  = parent::$SearchArray;
        $this->DbMap = new DbMap();

        foreach ($file_array as $k => $file) {
            $key = File::getVideoKey($file);

            if (\array_key_exists($key, $this->file_array)) {
                $movedFile = str_replace('/'.__LIBRARY__, '/Dupes/'.__LIBRARY__, $file);
                $dupePath  = \dirname($movedFile);
                $filename  = basename($file);

                $dupePath = nFileSystem::normalizePath($dupePath);
                if (!is_dir($dupePath)) {
                    //     if (!Option::isTrue('test')) {
                    nFileSystem::createDir($dupePath, 0755);
                    //     }
                }
                Mediatag::$output->writeln($file.'is dup');
                //  (new SfSystem())->rename($file, $dupePath.\DIRECTORY_SEPARATOR.$filename, true);
                continue;
            }
            $this->file_array[$key] = $file;
        }

        parent::$dbconn->file_array = $this->file_array;
        $this->db_array             = parent::$dbconn->getDbFileList();

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
