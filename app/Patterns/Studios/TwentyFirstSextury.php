<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Patterns\Studios\TwentyFirst;

const TWENTYFIRSTSEXTURY_REGEX_COMMON = '/([a-zA-Z0-9\-]+)\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i';

class TwentyFirstSextury extends Patterns
{
    public $studio       = '21st Sextury';
    public $network      = '21st Sextury';

    public $regex        = [
        'twentyfirstsextury' => [
            'artist' => [
                'pattern'             => TWENTYFIRSTSEXTURY_REGEX_COMMON,
                'delimr'              => '_',
                'match'               => 2,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                //    'studioPattern' => '/^([a-zA-Z]{1,5})_.*/i',
                'pattern' => TWENTYFIRSTSEXTURY_REGEX_COMMON,
                'match'   => 1,
                'delim'   => '_',
            ],
        ],
    ];
    // public function getNetwork()
    // {

    //     utmdump($this->network);
    //     $network = $this->mapStudio($this->network);
    //    // $network = $this->metaNetwork();
    //     $this->network =  $network ;
    //     utmdump($this->network);
    //     return  $this->network;
    // }
}
