<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Mediatag\Commands\Phdb\Lang;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Options extends MediaOptions
{
    use Lang;

    public $options = ['Default'=>null];


    public function Definitions()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        Translate::$Class = __CLASS__;
        return [
            ['convert', 'c', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],
            ['file', 'f', InputOption::VALUE_REQUIRED, Translate::text('L__PHDB_DESC')],
            ['map', 'm', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],

        ];

    }
    #


    public function Arguments($varName=null, $description = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
