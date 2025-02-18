<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Markers;

trait Markers
{
    public function videoDuration($duration)
    {
        // utminfo(func_get_args());

        $seconds = (int) round($duration);
        $secs    = $seconds % 60;
        $hrs     = $seconds / 60;
        $hrs     = floor($hrs);
        $mins    = $hrs % 60;
        $hrs /= 60;

        return \sprintf('%02d:%02d:%02d', $hrs, $mins, $secs);
    }

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
                $start = $this->videoDuration($row['timeCode']);
            }

            if (str_contains(strtolower($markerKey), 'end')) {
                $end = $this->videoDuration($row['timeCode']);

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
