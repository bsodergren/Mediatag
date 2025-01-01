<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

// use Intervention\Image\Image;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\VideoData;

class VideoPreview extends VideoData
{
    public $video_key;

    public $video_file;

    public $video_name;

    public $progressBar = false;

    public $preview_path;

    public $previewName;

    public $returnText;

    public $thumbType = 'preview';

    public $thumbExt = '.gif';
    public $thumbDir = __INC_WEB_PREVIEW_DIR__;

    public $VideoDataTable = __MYSQL_VIDEO_FILE__;

    public $actionText = '';

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = (string) $key;
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $preview = $this->BuildPreview();

        return ['preview' => $preview, 'video_key' => $this->video_key];
    }

    public function BuildPreview()
    {
        // utminfo(func_get_args());

        $this->previewName = $this->videoToThumb($this->video_file);
        $this->video_name  = basename($this->video_file);
        // $type             = $this->actionText;
        $action           = $this->updatedText;
        $this->returnText = $this->updatedText.$this->actionText;

        if (file_exists($this->previewName)) {
            // --$this->fileCount;
            $this->actionText = $action.$this->thumbType;

            return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
        }

        $this->preview_path = \dirname($this->previewName);
        (new Filesystem())->mkdir($this->preview_path);

        return $this->build_video_thumbnail();
    }
}
