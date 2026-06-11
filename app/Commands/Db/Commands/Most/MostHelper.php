<?php

namespace Mediatag\Commands\Db\Commands\Most;

use Mediatag\Commands\Db\Commands\Json\JsonHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;

trait MostHelper
{
    use JsonHelper;

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

    public function execJson()
    {
        $this->JsonExec();
        $this->jsonUpdates();
    }
}
