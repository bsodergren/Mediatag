<?php
namespace Mediatag\Commands\Clip\Commands\Merge;
/**
 * Command like Metatag writer for video files.
 */


use Mediatag\Commands\Clip\Helper;
use Mediatag\Core\MediaCommand;
use Mediatag\Commands\Clip\Lang;
use Mediatag\Core\Helper\MediaExecute;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'merge', description: 'Merge Command')]
class MergeCommand extends MediaCommand 
{
    // use Lang;
    // use MediaExecute;
    // use Helper;
    public const USE_LIBRARY = true;
    public const SKIP_SEARCH = true;

    public $command = [
        'merge'    => ['mergeFiles' => null,],
    ];
    // public $command = [
    //     'merge'           => [
    //         'exec'            => null,
    //         'getfileList'     => null,
    //         'mergeClips'      => null,
    //     ],
    // ];

    // public function MergeFunction()
    // {
    //     $list = $this->getfileList();
    //     utmdd($list);
    // }


}
