<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Modules\VideoData\Data\VideoInfo;

trait Helper
{


    
    public function execInfo()
    {
        // utminfo(func_get_args());

        $this->obj = new VideoInfo();
        // $this->checkClean();
        $this->obj->updateVideoData();
    }
}
