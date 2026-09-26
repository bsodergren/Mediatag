<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test\Commands\Export;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Modules\TagBuilder\Json\Reader as JsonReader;


trait ExportHelper
{
    public function exportJson()
    {

        $string = '{"title":"Double The Love","cast":["Nade Nasty","Danny Steele","Joey White"],"categories":["Blonde","Piercings","Threesome","Masturbation","Anal","Small Tits","Tattoos","Toys","Ass To Mouth","Pussy To Mouth","Blowjob","Deepthroat","Cumshot","Double Penetration","Cum In Mouth","Big Dick","Lingerie","Face Fucking","Gonzo","Foot Fetish","Oil","Straight","Shower","Caucasian","Doggy Style","Rimming"],"tags":["Blonde","Piercings","Threesome","Masturbation","Anal","Small Tits","Tattoos","Toys","Ass To Mouth","Pussy To Mouth","Blowjob","Deepthroat","Cumshot","Double Penetration","Cum In Mouth","Big Dick","Lingerie","Face Fucking","Gonzo","Foot Fetish","Oil","Straight","Shower","Caucasian","Doggy Style","Rimming"],"uploader":"Hussie Pass","duration":3104,"actionTags":"Twerking:304,Masturbating:369,Anal Toys:472,Oil:639,Oil:714,Twerking:785,Blowjob:919,Face Fucking:1015,Ball Sucking:1108,Footjob:1248,Doggystyle:1375,Rimming:1552,Daisy Chain:1667,Deepthroat:1713,Spanking:1720,Doggystyle:1723,Reverse Cowgirl:1890,Cowgirl:1987,Double Penetration:2058,Cowgirl:2163,Double Penetration:2254,Side Fuck:2418,Side Fuck:2570,Ball Sucking:2669,Cum on Face:2766,Cum in Mouth:2849,Blowjob:2905"}';
        $array = json_decode($string, true);
        $jsonArray = [];
        // utmdd($array);
        Mediatag::$Console->writeln('Hello ' . __METHOD__);

        foreach (parent::$SearchArray as $i => $file) {
            $video_key = MediaFile::getVideoKey($file, __LIBRARY__);
            $videoInfo = (new VideoTags())->get($video_key, $file);
            foreach ($videoInfo as $tag => $value) {
                $tag = ucfirst($tag);
                if(str_contains($value,",")){
                    $jsonArray[$tag] =  explode(',', $value);
                    continue;
                }
                $jsonArray[$tag] = $value;

            }
            $Marker = new Markers();
            $video_id = $Marker->getvideoId($video_key);
            if ($video_id !== null) {
                $query    = $Marker->videoQuery($video_id);
                $result   = Storage::$DB->query($query);
                if (\count($result) > 0) {

                    $jsonArray['actionTags'] = $this->getMarkerJson($result);

                }
            }

            $fileInfo = VideoFileInfo::getVidInfo($file);
            foreach ($fileInfo as $tag => $value) {
                $tag = ucfirst($tag);
                $jsonArray[$tag] = $value;

            }
            $jsonArray = JsonReader::convertJson($jsonArray);
            $json_string = json_encode($jsonArray,JSON_UNESCAPED_SLASHES);
            utmdd($json_string);

        }

        exit;
    }

    private function getMarkerJson($data)
    {

        //        "actionTags": "Twerking:304,Masturbating:369,Anal Toys:472,Oil:639,Oil:714,Twerking:785,Blowjob:919,Face Fucking:1015,Ball Sucking:1108,Footjob:1248,Doggystyle:1375,Rimming:1552,Daisy Chain:1667,Deepthroat:1713,Spanking:1720,Doggystyle:1723,Reverse Cowgirl:1890,Cowgirl:1987,Double Penetration:2058,Cowgirl:2163,Double Penetration:2254,Side Fuck:2418,Side Fuck:2570,Ball Sucking:2669,Cum on Face:2766,Cum in Mouth:2849,Blowjob:2905"

        foreach ($data as $i => $row) {
            $markerArray[] = $row['text'] . ':' . $row['timeCode'];
        }

        return   implode(',', $markerArray);
        //    return json_encode($arrray);


    }
}
