<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Markers;

use Mediatag\Modules\VideoData\Data\Duration;

trait Markers
{
    public function getVideoMarks($videoInfo)
    {
        $videoKey  = 0;
        $markers   = [];
        $markerPos = [];
        //utmdd($videoInfo);
        foreach ($videoInfo as $k => $row) {
            if (!\array_key_exists('timeCode', $row)) {
                return null;
            }

            if ($row['video_key'] != $videoKey) {
                $videoKey  = $row['video_key'];
                $markerIdx = 0;
            }

            $markers[$row['video_key']] = [
                'filename' => $row['file_name'],
            ];

            [$markerText,$markerKey] = explode('_', $row['markerText']);

            if (str_contains(strtolower($markerKey), 'start')) {
                $start = $this->Marker->videoDuration($row['timeCode']);
               // continue;
            // } else {

            //     if (!str_contains(strtolower($markerKey), 'end')) {
            //         $duration = new Duration();
            //         $ret = $duration->get($videoKey,$row['file_name']);
            //         $end = $this->Marker->videoDuration($ret['duration']);
                }

                if (str_contains(strtolower($markerKey), 'end')) {
                    $end = $this->Marker->videoDuration($row['timeCode']);

        $markerPos[$markerIdx] = [
            'text' => $markerText,
            'start'=> $start,
            'end'  => $end];
        $markerIdx++;
                }

               
        // }
        
                // $start = '';
            

           
            // $markerIdx++;

            $markers[$row['video_key']]['markers'] = $markerPos;
        }

        return $markers;
    }
}
