<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Preview;

use Mediatag\Modules\VideoInfo\Section\preview\GifPreviewFiles;

trait Helper
{
    public function execPreview()
    {
        // utminfo(func_get_args());

        $this->obj = new GifPreviewFiles();

        $this->checkClean();

        $this->obj->updateVideoData();
    }
}
