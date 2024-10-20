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
        'import'             => [
            'exec'         => null,
            'import'          => null,
            // 'writeChanges' => true,
        ],
            'split'          => [
                'exec'        => null,
                'splitDb' => null,
            ],
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
        utminfo();

        parent::boot($input, $output, ['SKIP_SEARCH' => true]);
        $this->ph_csv = $args;

        // parent::$Display              = new ShowDisplay($output);
    }

    public function exec($option = null)
    {
        utminfo();

        $path = __CURRENT_DIRECTORY__;
        if (str_contains($this->ph_csv, ",")) {
            $this->ph_csv = explode(",", $this->ph_csv);
        } else {

            MediaFinder::$depth = 1;


            if (Option::isTrue('map') || Option::isTrue('import')) {
                $fileSearch         = "ph_db*.txt";
                $path = __PORNHUB_TXT_DIR__;

            }
            if (Option::isTrue('convert')) {
                $fileSearch         = "ph_db*.csv";
                $path = __PORNHUB_CSV_DIR__;

            }

            if (Option::isTrue('split')) {
                $path = __CURRENT_DIRECTORY__;

            }

            if (Option::isTrue('file')) {
                $fileSearch         = Option::getValue('file');
            }
            $this->ph_csv       = MediaFinder::find($fileSearch, $path);
        }

    }

    public function print()
    {
    }


}
