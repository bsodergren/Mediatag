<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFinder;
use UTM\Utilities\Option;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends Mediatag
{
    use Helper;

    public $ph_csv              = null;
    public $commandList         = [
        'convert'         => [
            'exec'         => null,
            'convert'      => null,

        ],
        'map'             => [
            'exec'         => null,
            'map'          => null,
            // 'writeChanges' => true,
        ],
        //     'move'          => [
        //         'exec'        => null,
        //         'moveStudios' => null,
        //     ],
        //     'numberofFiles' => [
        //         'exec'             => null,
        //         'getNumberofFiles' => null,
        //     ],
        //     'list'          => [
        //         'exec'        => null,
        //         'getChanges'  => null,
        //         'saveChanges' => 'isset',
        //     ],
    ];

    public $defaultCommands     = [
        'exec'         => null,
        //     'getChanges'   => null,
        //     'writeChanges' => null,
    ];
    public function __construct(InputInterface $input = null, OutputInterface $output = null, $args = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        parent::boot($input, $output, ['SKIP_SEARCH' => true]);
        $this->ph_csv = $args;

        // parent::$Display              = new ShowDisplay($output);
    }

    public function exec($option = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);


        if (str_contains($this->ph_csv, ",")) {
            $this->ph_csv = explode(",", $this->ph_csv);
        }

        if (Option::isTrue('file')) {
            $fileSearch         = Option::getValue('file');
            MediaFinder::$depth = 1;
            $this->ph_csv       = MediaFinder::find($fileSearch, __CURRENT_DIRECTORY__);
        }
        // utmdd( $this->ph_csv );


    }

    public function print() {}


}
