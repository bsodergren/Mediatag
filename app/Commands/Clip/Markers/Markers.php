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
        $chapters    = [];
        $chapterPos  = [];
        $chapterIdx  = 0;
        $rowIdx     = 0;
        $rows       = count($videoInfo);
        //         $ChapStart  = $videoInfo[0];
        //         $ChapStart['duration']   = $this->videoDuration($ChapStart['timeCode'] - 1);

        //         $videoInfo               = \array_reverse($videoInfo);

        //         $ChapStart['timeCode']   = 0;
        //         $ChapStart['chapterText'] = 'Opening';
        //         unset($ChapStart['id']);
        // //        unset($ChapStart['duration']);

        //         \array_push($videoInfo, $ChapStart);
        //         $videoInfo = \array_reverse($videoInfo);
        foreach ($videoInfo as $k => $row) {
            if ($k == 0) {
                // utmdd($row);
            }

            // if( !isset($row['file_name']) ){
            //     return null;
            // }
            if (! array_key_exists('timeCode', $row)) {
                return null;
            }

            if ($row['video_key'] != $videoKey) {
                $videoKey  = $row['video_key'];
                $chapterIdx = 0;
            }

            $chapters[$row['video_key']] = [
                'filename' => $row['filename'],
            ];

            // [$chapterKey,$chapterText] = explode('Chapter', $row['chapterText']);

            // utmdd($chapterKey);
            // if (str_contains(strtolower($row['chapterText']), 'chapter')) {
            if ($chapterIdx == 0) {
                $chapterRow[] = [
                    'start' => 0,
                    'end'   => $videoInfo[$rowIdx + 1]['timeCode'] - 1,
                    'text'  => $row['text'],
                ];
                $chapterIdx++;
                $rowIdx++;

                continue;
            }
            $chapterRow[$chapterIdx]['start'] = (int) $row['timeCode'];

            if (array_key_exists($rowIdx + 1, $videoInfo)) {
                $chapterRow[$chapterIdx]['end'] = $videoInfo[$rowIdx + 1]['timeCode'] - 1;
            } else {
                $chapterRow[$chapterIdx]['end'] = $row['duration'] / 1000;
            }

            $chapterRow[$chapterIdx]['text'] = $row['text'];

            // } else {
            //     $end = $videoInfo[$k-1]['timeCode'] - 1;
            //     $start = $row['timeCode'];
            // }
            $chapters[$row['video_key']]['chapters'] = $chapterRow;
            $chapterIdx++;
            // }
            $rowIdx++;

            // if (str_contains(strtolower($chapterKey), 'end')) {
            //     $end = $row['timeCode'];
            //         // $end = $this->videoDuration($end);

            //     $chapterPos[$chapterIdx] = [
            //         'text' => $chapterText,
            //         'start'=> $start,
            //         'end'  => $end];
            //     ++$chapterIdx;
            // }
        }

        // utmdd("f");

        return $chapters;
    }

    public function getVideoMarks($videoInfo)
    {
        $videoKey  = 0;
        $markers   = [];
        $markerPos = [];
        $total     = count($videoInfo);
        foreach ($videoInfo as $k => $row) {
            if (! array_key_exists('timeCode', $row)) {
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
                $start   = $row['timeCode'];
                $start   = $this->videoDuration($start);
                $endMark = true;
            }

            if (str_contains(strtolower($markerKey), 'end')) {
                $end                   = $row['timeCode'];
                $end                   = $this->videoDuration($end);
                $endMark               = false;
                $markerPos[$markerIdx] = [
                    'text'  => $markerText,
                    'start' => $start,
                    'end'   => $end];
                $markerIdx++;
            }

            if ($k + 1 == $total && $endMark === true) {
                $end                   = $row['timeCode'] + 100;
                $end                   = $this->videoDuration($end);
                $markerPos[$markerIdx] = [
                    'text'  => $markerText,
                    'start' => $start,
                    'end'   => $end];
            }

            $markers[$row['video_key']]['markers'] = $markerPos;
        }

        return $markers;
    }
}
