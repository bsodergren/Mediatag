<?php

namespace Mediatag\Commands\Db\Commands\Info;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;

trait InfoHelper
{
    public function infoMethod()
    {
        $this->obj = new VideoFileInfo;
        $this->checkClean();
        $this->obj->updateVideoData();
    }
}
