<?php

namespace Mediatag\Commands\Db\Commands\Json;

use FFMpeg\FFMpeg;
use Mediatag\Bundle\Grephp\Grephp;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;

trait StudioJsonHelper
{
    public function loadVideoJson()
    {
        $db           = MysqliDb::getInstance();
        $fileLocation = __PLEX_STUDIO_JSON_DIR__ . DIRECTORY_SEPARATOR . 'adulttime';
        $files        = MediaFinder::find('*.json', $fileLocation);
        foreach ($files as $file) {
            $videoName = basename($file, '.json');
            $videoName = str_replace('_', '', $videoName);
            $query     = 'SELECT * FROM `mediatag_video_metadata` where ';

            $query .= "LOWER(REPLACE(title,' ','')) = LOWER('" . $videoName . "') OR ";
            $query .= " video_key = '" . $videoName . "'";

            $result = $db->query($query);

            $video_id = (new Markers)->getvideoId($result[0]['video_key']);
            // utmdump([$result, $query]);
            if (! is_null($video_id)) {
                $jsonData = file_get_contents($file);
                $jsonData = str_replace("\n", ',', $jsonData);
                // $markerArray = implode(',', $jsonData);
                // $json     = json_decode($jsonData, true);
                // utmdd($jsonData);
                // $markers = $json['markers'] ?? null;
                $this->myupdateVideoMarkers($result[0], $jsonData, $video_id);
            } else {
                parent::$output->writeln('<comment> skipping ' . basename($file) . ' ' . $videoName . '</comment>');
            }
        }

        // $filelist_array = $this->VideoList['file'];
        // foreach ($filelist_array as $key => $row) {
        //     $videoId = VideoInfo::GetVideoIdByKey($key);
        //     utmdump($videoId);
        // }
    }

    public function myupdateVideoMarkers($videoInfo, $markerArray, $id)
    {
        if (is_null($markerArray)) {
            return false;
        }
        $dbConn   = MysqliDb::getInstance();
        $video_id = (new Markers)->getvideoId($videoInfo['video_key']);

        $markers = explode(',', $markerArray);

        foreach ($markers as $marker) {
            $parts = explode(':', $marker);
            $data  = [
                'timeCode'   => round($parts[1], 0),
                'video_id'   => $video_id,
                'markerText' => $parts[0],
            ];
            $dbConn->where('timeCode', round($parts[1], 0));
            $dbConn->where('markerText', $parts[0]);
            $dbConn->where('video_id', $video_id);
            $res = $dbConn->getone(__MYSQL_VIDEO_MARKERS__);
            if (is_null($res)) {
                $dbConn->insert(__MYSQL_VIDEO_MARKERS__, $data);
                parent::$output->writeln(' <id>Updating markers for ' . basename($videoInfo['title']) . '</id>');
            }
        }
    }
}
