<?php

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Commands\Db\Process;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;

class ThumbnailProcess extends Process
{
    public function execThumb()
    {
        // utminfo(func_get_args());
        // utmdd($this->video_file);
        //
        $this->obj = new Thumbnail;
        $this->checkClean();
        // $this->obj = new Thumbnail(parent::$input, parent::$output);
        $this->obj->updateVideoData();
    }
}
