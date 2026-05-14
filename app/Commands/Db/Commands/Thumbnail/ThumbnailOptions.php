<?php

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Commands\Db\Lang;
use Mediatag\Commands\Db\Options;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ThumbnailOptions extends Options
{
    use Lang;
    use Translate;

    public $options = ['Test'];

    public function Definitions()
    {
        self::$Class   = __CLASS__;
        $parentOptions = parent::Definitions();
        $options       = [
            ['break'],
        ];

        return array_merge($parentOptions, $options);
    }
}
