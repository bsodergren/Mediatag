<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Markers;

use function array_key_exists;
use function count;
use function sprintf;

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

        return sprintf('%02d:%02d:%02d', $hrs, $mins, $secs);
    }

    public function getVideoChapters($videoInfo)
    {
        $videoKey   = 0;
        $chapterRow = [];
        $markers    = [];
        $markerPos  = [];
        $markerIdx  = 0;
        $rowIdx     = 0;

        $rows = count($videoInfo);
        foreach ($videoInfo as $k => $row) {
            // if( !isset($row['file_name']) ){
            //     return null;
            // }
            if (!array_key_exists('timeCode', $row)) {
                return null;
            }

            if ($row['video_key'] != $videoKey) {
                $videoKey  = $row['video_key'];
                $markerIdx = 0;
            }

            $markers[$row['video_key']] = [
                'filename' => $row['filename'],
            ];

            // [$markerKey,$markerText] = explode('Chapter', $row['markerText']);

            // utmdd($markerKey);
            if (str_contains(strtolower($row['markerText']), 'chapter')) {
                if (0 == $markerIdx) {
                    $chapterRow[$markerIdx]['start'] = 0;
                    $chapterRow[$markerIdx]['end']   = $videoInfo[$rowIdx + 1]['timeCode'] - 1;
                    $chapterRow[$markerIdx]['text']  = $row['markerText'];
                    ++$markerIdx;
                    ++$rowIdx;
                    continue;
                }

                $chapterRow[$markerIdx]['start'] = (int) $row['timeCode'];

                if (array_key_exists($rowIdx + 1, $videoInfo)) {
                    $chapterRow[$markerIdx]['end'] = $videoInfo[$rowIdx + 1]['timeCode'] - 1;
                } else {
                    $chapterRow[$markerIdx]['end'] = $row['duration'] / 1000;
                }

                $chapterRow[$markerIdx]['text'] = $row['markerText'];

                // } else {
                //     $end = $videoInfo[$k-1]['timeCode'] - 1;
                //     $start = $row['timeCode'];
                // }
                $markers[$row['video_key']]['markers'] = $chapterRow;
                ++$markerIdx;
            }
            ++$rowIdx;

            // if (str_contains(strtolower($markerKey), 'end')) {
            //     $end = $row['timeCode'];
            //         // $end = $this->videoDuration($end);

            //     $markerPos[$markerIdx] = [
            //         'text' => $markerText,
            //         'start'=> $start,
            //         'end'  => $end];
            //     ++$markerIdx;
            // }
        }
        // utmdd($markers);

        // utmdd("f");
        return $markers;
    }

    public function getVideoMarks($videoInfo)
    {
        $videoKey  = 0;
        $markers   = [];
        $markerPos = [];
        foreach ($videoInfo as $k => $row) {
            if (!array_key_exists('timeCode', $row)) {
                return null;
            }

            if ($row['video_key'] != $videoKey) {
                $videoKey  = $row['video_key'];
                $markerIdx = 0;
            }

            $markers[$row['video_key']] = [
                'filename' => $row['filename'],
            ];

            [$markerText,$markerKey] = explode('_', $row['markerText']);

            if (str_contains(strtolower($markerKey), 'start')) {
                $start = $row['timeCode'];
                $start = $this->videoDuration($start);
            }

            if (str_contains(strtolower($markerKey), 'end')) {
                $end = $row['timeCode'];
                $end = $this->videoDuration($end);

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
