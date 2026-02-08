<?php

namespace Mediatag\Commands\Db\Commands\Captions;

use Mediatag\Core\Mediatag;

trait CaptionsHelper
{
    public function captionsMethod()
    {
        Mediatag::$Console->writeln("Hello ". __METHOD__);
        exit;
    }
}
