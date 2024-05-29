<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Show;

use UTM\Bundle\Monolog\UTMLog;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\ShowDisplay;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;

    public $VideoData;

    public $missing     = [];

    public $meta;

    public $VideoList   = [];

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

    public function __construct(InputInterface $input = null, OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);
        // parent::$Display              = new ShowDisplay($output);
    }

    public function __call($m, $a)
    {
        UTMLog::logger('call', $m);

        return null;
    }

    public function exec($option = null)
    {
        // $meta = new metaReader($this->videoData);
        // return $meta->getTagArray();
        $this->VideoList = parent::getVideoArray();
        UTMLog::logger('Video List', \count($this->VideoList));
    }

    public function print()
    {
        $filelist_array                = $this->VideoList['file'];
        Mediatag::$Display->LineBreaks = true;
        Mediatag::$Display->DisplayTable($filelist_array);
    }

    public function return()
    {
        $this->meta['print'] = true;

        return $this->meta;
    }
}
