<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\DbMap;
use Mediatag\Modules\Database\TagDB;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function define;

/**
 * Summary of MediamapProcess.
 */
class Process extends Mediatag
{
    use Helper;

    // public $defaultCommands = [
    //     'init' => null,
    //     'exec' => null,
    // ];

    public $commandList = [
        'search'    => ['searchDBEntry' => true],
        'channel'   => ['addStudioChannelEntry' => true],
        'artist'    => ['addartistentry' => true],
        'title'     => ['addTitleEntry' => true],
        'genre'     => ['genreMap' => true],
        'video'     => ['videoTag' => true],
        'keyword'   => ['addartistentry' => true],
        'lang'      => ['AddLangugage' => null],
        'list'      => ['listMap' => null],
        'word'      => ['addText' => null],
        'artistMap' => ['artistMap' => null],
    ];

    private $global_lang = __APP_HOME__ . '/app/Locales/Lang.php';

    private $command_lang = __APP_HOME__ . '/app/Commands/%KEY%/Lang.php';

    public $tagConn;

    public $StorageConn;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        // utminfo(func_get_args());

        define('USE_SEARCH', true);

        parent::__construct($input, $output);

        // $this->output = $output;
        // $this->input  = $input;
        $this->tagConn     = new TagDB;
        $this->StorageConn = new DbMap;
    }

    public function exec($option = null) {}

    public function print() {}
}
