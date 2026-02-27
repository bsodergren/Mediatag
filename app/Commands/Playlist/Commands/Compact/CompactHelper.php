<?php

namespace Mediatag\Commands\Playlist\Commands\Compact;

use Mediatag\Core\Mediatag;

trait CompactHelper
{
    public function compactMethod()
    {
        Mediatag::$Console->writeln('Hello ' . __METHOD__);
        exit;
    }
}
