<?php

namespace Mediatag\Commands\Db\Commands\Preview;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\JpgPreviewFiles;

trait PreviewHelper
{
    public function previewMethod()
    {
        $this->obj = new GifPreviewFiles;
        $this->checkClean();
        $this->obj->updateVideoData();
    }
}
