<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;

trait Helper
{
    public function execInfo()
    {
        // utminfo(func_get_args());

        $this->obj = new VideoFileInfo();
        // $this->checkClean();
        $this->obj->updateVideoData();
    }
}
