<?php

namespace Mediatag\Commands\Test\Commands\Export;

use Mediatag\Commands\Test\Lang;
use Mediatag\Commands\Test\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ExportOptions extends Options
{
    use Lang;
    use Translate;

    public $options = ['Default','Test'];

    public function Definitions()
    {
        self::$Class   = __CLASS__;
                $parentOptions = parent::Definitions();
                $options       = [
                    // ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L_OPTION_OVERWRITE')],
                    ['break'],
                ];

                return array_merge($parentOptions, $options);
    }
}
