<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Captions;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;

trait Helper
{
    public function execCaptions()
    {
        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.vtt');

        foreach ($file_array as $file) {
            $video_file = str_replace('.vtt', '.mp4', $file);
            $videoKey   = MediaFile::file($video_file, 'videokey');
            $exists     = parent::$dbconn->videoExists($videoKey, null, __MYSQL_VIDEO_INFO__);

            utmdd($exists);
        }
    }
}
