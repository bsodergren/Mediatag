<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use Mediatag\Core\Helper\MediaExecute;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;
    use MediaExecute;

    public $VideoData;

    public $missing = [];

    public $meta;

    public $VideoList = [];

    public $commandList = [
        'missing'    => [
            'exec'        => null,
            'findMissing' => ['missing' => true],
        ],
        'new'        => [
            'newFiles' => null,
        ],
        'duplicates' => [
            'duplicateFiles' => null,
        ],

        'playlist'   => [
            'exec'           => null,
            'createPlaylist' => null,
        ],
    ];
    protected $useFuncs = ['addMeta'];

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);
    }

    public function __call($m, $a)
    {
        return null;
    }

    public function exec($option = null)
    {
        $this->VideoList = parent::getVideoArray();
    }

    public function print()
    {
        // utminfo(func_get_args());

        $filelist_array                = $this->VideoList['file'];
        Mediatag::$Display->LineBreaks = true;
        Mediatag::$Display->DisplayTable($filelist_array);
    }

    public function return()
    {
        // utminfo(func_get_args());

        $this->meta['print'] = true;

        return $this->meta;
    }
}
