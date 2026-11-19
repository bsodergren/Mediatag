<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Playlist\Commands\Compact;

use Mediatag\Commands\Playlist\Helper;
use Mediatag\Commands\Playlist\Traits\CompactPlaylist;
use Mediatag\Core\Mediatag;

trait CompactHelper
{
    use Helper;
    use CompactPlaylist;


    public function compactMethod()
    {
        $this->docompactPlaylist(true);
        exit;
    }


}
