<?php

namespace Mediatag\Commands\Db\Commands\All;

use Mediatag\Commands\Db\Process;
use Mediatag\Modules\VideoInfo\Section\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;

class AllProcess extends Process
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

    public function execInfo()
    {
        // utminfo(func_get_args());
        $this->obj = new VideoFileInfo;
        // $this->checkClean();
        $this->obj->updateVideoData();
    }

    public function execPreview()
    {
        // utminfo(func_get_args());

        $this->obj = new GifPreviewFiles;

        $this->checkClean();

        $this->obj->updateVideoData();
    }
}
