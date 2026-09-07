<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Chapter;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use UTM\Utilities\Option;

trait ChapterHelper
{
    public function chapterMethod()
    {
        $this->obj = new VideoFileInfo();
        $this->checkClean();
        $this->obj->thumbType = 'markers';
        $this->obj->VideoDataTable = __MYSQL_VIDEO_MARKERS__;

        $this->obj->updateVideoData();
    }
}
