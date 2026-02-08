<?php

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;

trait ThumbnailHelper
{
    public function thumbnailMethod()
    {
        $this->obj = new Thumbnail;
        $this->checkClean();
        $this->obj->updateVideoData();
    }
}
