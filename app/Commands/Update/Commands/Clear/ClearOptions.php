<?php

namespace Mediatag\Commands\Update\Commands\Clear;

use Mediatag\Commands\Update\Lang;
use Mediatag\Commands\Update\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ClearOptions extends Options
{
    use Lang;
    use Translate;

    public $options = ['Default', 'Meta','Test'];

    public function Definitions()
    {
        self::$Class   = __CLASS__;
        $parentOptions = parent::Definitions();
        $options       = [
            ['clearMeta', '', InputOption::VALUE_REQUIRED, self::text('L__RENAME_TRANS')],

            // ['overwrite', 'o', InputOption::VALUE_NONE, self::text('L_OPTION_OVERWRITE')],
            ['break'],
        ];

        return array_merge($parentOptions, $options);
    }
}
