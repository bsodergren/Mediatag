<?php

namespace Mediatag\Commands\Playlist\Commands\Compact;

use Mediatag\Commands\Playlist\Helper;
use Mediatag\Core\Mediatag;

trait CompactHelper
{
    use Helper;

    public function compactMethod()
    {
        $this->docompactPlaylist(true);
        exit;
    }
}
