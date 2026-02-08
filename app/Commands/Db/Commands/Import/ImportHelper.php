<?php

namespace Mediatag\Commands\Db\Commands\Import;

use Mediatag\Core\Mediatag;

trait ImportHelper
{
    public function importMethod()
    {
        Mediatag::$Console->writeln("Hello ". __METHOD__);
        exit;
    }
}
