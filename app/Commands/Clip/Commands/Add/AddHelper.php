<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Add;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Modules\VideoData\Data\VideoInfo;
use UTM\Utilities\Option;

trait AddHelper
{
    public $Marker;
    public $markerArray;

    public function addMarker()
    {
        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $this->addMarkerToVideo($vidArray);
        }
    }

    public function addMarkerToVideo($Video)
    {
        $prev_time = 0;
        $time      = Option::getValue('time');
        $name      = Option::getValue('name', true);
        $duration  = (VideoInfo::getVidInfo($Video['video_file'])['duration'] / 1000);

        $video_id = (new Markers())->getvideoId($Video['video_key']);
        // utmdd($this->VideoList);

        $suffix = ['Start', 'End'];
        foreach ($time as $i => $t) {
            $mod = 0;
            if (str_contains($t, '+')) {
                $t   = ltrim($t, '+');
                $mod = $t;
                $t   = $prev_time;
            }
            $seconds = $this->timeCodetoSec($t, $mod);

            if ($duration < $seconds) {
                if ('Start' == $suffix[$i]) {
                    $seconds = 0;
                } else {
                    $seconds = $duration - 1;
                }
            }
            $data = [
                'timeCode'       => round($seconds, 0),
                'video_id'       => $video_id,
                'markerText'     => $name.'_'.$suffix[$i],
            ];

            $res = Mediatag::$dbconn->insert($data, __MYSQL_VIDEO_CHAPTER__);
            Mediatag::$output->writeln('<comment> Added tag '.$name.'</> at <fg=green>'.$suffix[$i].' at '.$seconds.'</>');

            //            Mediatag::$output->writeln('<comment> Added tag '.$name.'</> at <fg=green>'.$start_time.' and '.$end_time.'</>');
            $prev_time = $t;
            // utmdd($data);
        }

        // $start = $time[0];
        // $end = $time[1];

        //
        // $data = [
        //     'timeCode'       => $this->data['timeCode'],
        //     'video_id'       => $this->data['videoId'],
        //     'markerText'     => $this->data['markerText'],
        // ];
        // $res  = Mediatag::$dbconn->insert(__MYSQL_VIDEO_CHAPTER__, $data);
    }
}
