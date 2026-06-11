<?php

namespace Mediatag\Commands\Db\Commands\Thumbnail;

use FFMpeg\FFMpeg;
use Mediatag\Bundle\Grephp\Grephp;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

trait ThumbnailHelper
{
    use MediaFFmpeg;

    public function thumbnailMethod()
    {
        if (Option::isTrue('markers')) {
            parent::$output->writeln(' <error>Breaking out of process</error>');

            $this->markerThumbnail();

            return;
        }

        $this->obj = new Thumbnail;
        $this->checkClean();
        $this->obj->updateVideoData();
    }

    private function createMarkerThumb($videoid)
    {
        $db = MysqliDb::getInstance();

        $db->where('id', $videoid);
        $videoinfo = $db->getone(__MYSQL_VIDEO_FILE__, ['filename', 'fullpath']);

        $videoFile = $videoinfo['fullpath'] . DIRECTORY_SEPARATOR . $videoinfo['filename'];

        $db->where('video_id', $videoid);
        $res = $db->get(__MYSQL_VIDEO_MARKERS__);
        foreach ($res as $marker) {
            $timeCode         = $marker['timeCode'];
            $thumbnailDirPath = __INC_WEB_THUMB_DIR__ . DIRECTORY_SEPARATOR . 'markers' . DIRECTORY_SEPARATOR . $videoid;

            (new MediaFilesystem)->mkdir($thumbnailDirPath);
            $markerText    = str_replace(' ', '_', $marker['markerText']) . '_';
            $thumbnailFile = $thumbnailDirPath . DIRECTORY_SEPARATOR . 'marker_' . $markerText . $timeCode . '.jpg';
            $this->ffmegCreateThumb($videoFile, $thumbnailFile, $timeCode, '160:120');

            $thumbUrl = str_replace(__INC_WEB_THUMB_DIR__, __INC_WEB_THUMB_URL__, $thumbnailFile);
            $db->where('id', $marker['id']);
            $db->update(__MYSQL_VIDEO_MARKERS__, ['markerThumbnail' => $thumbUrl]);
            parent::$output->writeln(' <id>Creating marker Thumbnail ' . $marker['markerText'] . ' for ' . $videoid . ' </id>');
        }
    }

    public function markerThumbnail()
    {
        foreach (StorageDB::$DB->getDbFileList() as $key => $videoInfo) {
            $videoId = VideoInfo::GetVideoIdByKey($key);
            if (! is_null($videoId)) {
                $this->createMarkerThumb($videoId);
            }
        }

        //        utmdump($res);
    }
}
