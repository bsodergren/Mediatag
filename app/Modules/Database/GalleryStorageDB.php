<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\Section\Gallery;

use function is_array;

class GalleryStorageDB extends StorageDB
{
    public function createDbEntry($video_file, $video_key)
    {
        // utminfo(func_get_args());

        $this->init($video_file);

        $data = [
            'video_key' => $video_key,
            'filename'  => $this->video_name,
            'fullpath'  => $this->video_path,
            'Library'   => __LIBRARY__,
            'filesize'  => filesize($video_file),
        ];

        $data['added'] = $this->dbConn->now();

        return $data;
    }

    public function updateDBEntry($key, $videoData, $all = true)
    {
        // utminfo(func_get_args());

        $video_file                   = $videoData['video_file'];
        $video_id                     = true;
        $exists                       = $this->videoExists($key);
        Mediatag::$Display->BlockInfo = ['No' => '<info>' . $this->MultiIDX . '</info>'];
        $videoBlockInfo               = null;
        $action                       = '<comment>Updated</comment> ';

        if ($exists === null) {
            $data_array = $this->createDbEntry($video_file, $key);
            $video_id   = $this->insert($data_array);
            if ($video_id !== null) {
                $query = 'insert into ' . __MYSQL_VIDEO_SEQUENCE__ . ' (seq_id,video_id,video_key,Library) values ';
                $query .= " (nextseq('" . __LIBRARY__ . "')," . $video_id . ",'" . $key . "','" . __LIBRARY__ . "')";
                $this->query($query);

                $action = '<comment>Added</comment> ';
            } else {
                $action = '<error>Duplicate</error> ';
            }
        }

        Mediatag::$Display->BlockInfo['Video'] = $action . basename($video_file) . ' ';
        if ($video_id !== null) {
            // $this->vtags = new VideoTags();
            Mediatag::$Display->BlockInfo['MetaTags'] = (new Gallery)->getVideoInfo($key, $video_file);
            // $this->vinfo = new VideoInfo();
            //
            // if (true === $all) {

            //     // $this->thumb = new Thumbnail();
            //     Mediatag::$Display->BlockInfo['thumbnail'] = (new Thumbnail())->getVideoInfo($key, $video_file);

            //     Mediatag::$Display->BlockInfo['VideoInfo'] = (new VideoInfo())->getVideoInfo($key, $video_file);

            //     // $this->duration = new Duration();
            //     Mediatag::$Display->BlockInfo['Duration']  = (new Duration())->getVideoInfo($key, $video_file);

            //     // $this->preview = new GifPreviewFiles();
            //     Mediatag::$Display->BlockInfo['Preview']   = (new GifPreviewFiles())->getVideoInfo($key, $video_file);

            // }
        }

        foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
            $value = trim($value);

            $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=yellow');
        }
        if (is_array($videoBlockInfo)) {
            $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->writeln($videoBlockInfo);
            //  Mediatag::$Display->VideoInfoSection->writeln("");
        }
    }
}
