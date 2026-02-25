<?php

namespace Mediatag\Commands\Db\Commands\Empty;

use Mediatag\Commands\Db\Lang;
use Mediatag\Commands\Db\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EmptyOptions extends Options
{
    use Lang;
    use Translate;

    public $options = ['Test'];

    public function Definitions()
    {
        self::$Class   = __CLASS__;
        $parentOptions = parent::Definitions();
        $options       = [
            ['type', 'T', InputOption::VALUE_OPTIONAL, self::text('L_OPTION_OVERWRITE')],
            ['break'],
        ];

        return array_merge($parentOptions, $options);
    }
}
