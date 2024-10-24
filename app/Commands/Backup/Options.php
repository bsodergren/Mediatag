<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Backup;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Options extends MediaOptions
{
    use Lang;
    // public $options                          = ['Default'];

    public function Definitions()
    {
        utminfo(func_get_args());

        Translate::$Class = __CLASS__;

        return [
            ['db', '', InputOption::VALUE_NONE, Translate::text('L__BACKUP_DATABASE')],

            ['directory', 'D', InputOption::VALUE_REQUIRED, Translate::text('L__BACKUP_DIRECTORY')],
            ['backup', 'b', InputOption::VALUE_REQUIRED, Translate::text('L__BACKUP_TYPE')],
        ];
    }

    public function Arguments($varName = null, $description = null)
    {
        utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, Translate::text('L__BACKUP_DATABASE_NAME')];
    }
    // public function DefaultOptions()
    // {
    //     Translate::$Class = __CLASS__;

    //     $options = [
    //         ['range', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, Translate::text('L_FAULT_RANGE')],
    //     ];

    //     return self::getOptions($options);
    // }

    //    */

    /*
    public function Arguments($varName=null,$description = null)
    {
 utminfo(func_get_args());

        return [$varName, InputArgument::OPTIONAL, $description];
    }
#    */
}
