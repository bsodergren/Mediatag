<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Subtitles;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\StorageDB;

trait Helper
{
    public function execSubtitles()
    {
        $file_array = Mediatag::$finder->Search(__CURRENT_DIRECTORY__, '*.vtt', exit: false);
        $storagedb  = new StorageDB();

        if(!empty($file_array)){
        // utminfo(func_get_args());
        foreach ($file_array as $file) {
            $videoFile = $this->vtt2mp4($file);
            if($videoFile === null){
                continue;
            }
            $storagedb->init($videoFile);
            $data = [
                'subtitle' => 1,
            ];
            $where = ['video_key' => $storagedb->video_key];

            Mediatag::$dbconn->update($data, $where, __MYSQL_VIDEO_INFO__);
        }
    }
    }

    private function vtt2mp4($vtt)
    {
        $file = str_replace('.en.vtt', '.mp4', $vtt);
        $file = str_replace('/Subtitles', '', $file);

        if (is_file($file)) {
            return $file;
        }

        return null;
    }
}
