<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Add;

use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Traits\ffmpegTransition;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\Chooser;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait AddHelper
{
    public $Marker;
    public $markerArray;


    public function addMarker()
    {
        $time = Option::getValue('time');
        $name = Option::getValue('name', true);
// utmdd($time,$name);
        $video_id = (new Markers())->getvideoId(key($this->VideoList['file']));
// utmdd($this->VideoList);
        // utmdd($video_id);
        $suffix = ['Start', 'End'];
        foreach ($time as $i => $t) {
            $data = [
                'timeCode'       => $this->timeCodetoSec($t),
                'video_id'       => $video_id,
                'markerText'     => $name.'_'.$suffix[$i],
            ];

            $res = Mediatag::$dbconn->insert($data, __MYSQL_VIDEO_CHAPTER__);
            // utmdd($data);

            utmdump($res);
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
