<?php

namespace Mediatag\Commands\Playlist\Commands\Split;

use Mediatag\Commands\Playlist\Helper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Utilities\Option;

trait SplitHelper
{
    use Helper;

    public function splitMethod()
    {
        (int) $split = Option::getValue('splitlines');
        $splitName   = basename($this->playlist, '.txt');

        MediaFile::splitFile($this->playlist, './batch/', $split, $splitName . '_', '.txt');

        exit;
    }
}
