<?php
namespace Mediatag\Commands\Db\Commands\Thumbnail;

use Mediatag\Modules\VideoData\Data\Thumbnail;


trait Helper
{
    public function execThumb()
    {
        // utminfo(func_get_args());

        $this->obj = new Thumbnail();
        $this->checkClean();
        // $this->obj = new Thumbnail(parent::$input, parent::$output);
        $this->obj->updateVideoData();
    }
    
}