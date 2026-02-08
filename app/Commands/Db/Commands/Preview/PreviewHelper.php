<?php

namespace Mediatag\Commands\Db\Commands\Preview;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\GifPreviewFiles;

trait PreviewHelper
{
    public function previewMethod()
    {
        $this->obj = new GifPreviewFiles;
        $this->checkClean();
        $this->obj->updateVideoData();
    }
}
