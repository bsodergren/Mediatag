<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create\Commands\Add;

use Mediatag\Commands\Create\Lang;
use Mediatag\Commands\Create\Options;
use Mediatag\Traits\Translate;

class AddOptions extends Options
{
    use Lang;
    use Translate;

    public $options = [];

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
