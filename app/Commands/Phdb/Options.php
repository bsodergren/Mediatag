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

define("__PORNHUB_DB_DIR__", __CACHE_DIR__ . "/phdb");
define("__PORNHUB_RAW_DIR__", __PORNHUB_DB_DIR__ . "/raw");

define("__PORNHUB_CSV_DIR__", __PORNHUB_DB_DIR__ . "/csv");
define("__PORNHUB_TXT_DIR__", __PORNHUB_DB_DIR__ . "/txt");
define("__PORNHUB_FINISHED_DIR__", __PORNHUB_DB_DIR__ . "/finished");

define("__PORNHUB_DB_MAP_FILE__", __APP_HOME__ . "/app/Traits/CaseHelper.php");





class Options extends MediaOptions
{
    use Lang;

    public $options = ['Default'=>null];


    public function Definitions()
    {
        utminfo(func_get_args());

        Translate::$Class = __CLASS__;
        return [
            ['split', 's', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],

            ['convert', 'c', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],
            ['file', 'f', InputOption::VALUE_REQUIRED, Translate::text('L__PHDB_DESC')],
            ['map', 'm', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],
            ['import', 'i', InputOption::VALUE_NONE, Translate::text('L__PHDB_DESC')],

        ];

    }
    #


    public function Arguments($varName=null, $description = null)
    {
        utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
