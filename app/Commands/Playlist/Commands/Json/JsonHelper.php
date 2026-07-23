<?php

namespace Mediatag\Commands\Playlist\Commands\Json;

use Mediatag\Commands\Playlist\Helper;
use Mediatag\Core\Mediatag;
use Nette\Utils\Strings;

trait JsonHelper
{
    use Helper;

    public function JsonMethod()
    {
        $this->youtubeGetJsonFile();
// $this->youtubeGetJson($videoKey);
        
    // utmdd( $videoKey);

        exit;
    }
}
