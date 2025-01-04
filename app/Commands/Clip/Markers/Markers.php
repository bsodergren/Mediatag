<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Markers;

trait Markers
{
    public function getVideoMarks($videoInfo)
    {
        $videoKey  = 0;
        $markers   = [];
        $markerPos = [];
        // utmdd($videoInfo);
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
            }

            if (str_contains(strtolower($markerKey), 'end')) {
                $end = $this->Marker->videoDuration($row['timeCode']);

                $markerPos[$markerIdx] = [
                    'text' => $markerText,
                    'start'=> $start,
                    'end'  => $end];
                ++$markerIdx;
            }

            $markers[$row['video_key']]['markers'] = $markerPos;
        }

        return $markers;
    }
}
