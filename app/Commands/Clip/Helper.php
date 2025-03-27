<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use UTM\Utilities\Option;
use Mediatag\Utilities\Chooser;
use Mediatag\Traits\ffmpegTransition;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Core\Mediatag;
use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;

trait Helper
{
    use ffmpegTransition;
    use MarkerHelper;
    use MediaFFmpeg;
    public $Marker;
    public $markerArray;

    public function timeCodetoSec($time, $mod = 0)
    {
        $pcs     = explode(':', $time);
        $seconds = 0;
        $minutes = 0;
        $hours   = 0;

        rsort($pcs);
        $seconds = $pcs[0];
        if (\array_key_exists(1, $pcs)) {
            $minutes = $pcs[1] * 60;
        }
        if (\array_key_exists(2, $pcs)) {
            $hours = $pcs[2] * 60 * 60;
        }

        $time = ($seconds + $minutes + $hours);

        return $time + $mod;
    }

    public function getClipDirectory($filename, $level = 1)
    {
        $outputFile = str_replace('/XXX', '/Clips', $filename);
        if (0 == $level) {
            return $outputFile;
        }

        return \dirname($outputFile, $level);
    }

    public function getClipFilename($filename)
    {
        return $this->getClipDirectory($filename).\DIRECTORY_SEPARATOR.basename($filename);
    }

    public function setClipFilename($name)
    {
        $name = str_replace(' ', '_', $name);

        $filename = __LIBRARY_HOME__.\DIRECTORY_SEPARATOR.'Home Videos'.\DIRECTORY_SEPARATOR.'Compilation'.\DIRECTORY_SEPARATOR.$name.'.mp4';
        Filesystem::createDir(\dirname($filename));

        if (file_exists($filename)) {
            if (Chooser::changes(' Overwrite File ', 'overwrite', __LINE__)) {
                unlink($filename);
                // } else {
                //     exit;
            }
        }

        $filename = MediaFile::getFilename($filename);

        return $filename;
    }

    public function setffmpegFilename($name)
    {
        return $this->getClipDirectory(__CURRENT_DIRECTORY__, 0).\DIRECTORY_SEPARATOR.$name.'.txt';
    }




    
    public function getfileList()
    {
        $markerArray   = [];
        $this->FileIdx = 0;


        $search = Option::getValue('clip', true);
        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $this->Marker = new Markers();

            $this->Marker->getvideoId($key);

            if (null !== $this->Marker->video_id) {
                $query  = $this->Marker->videoQuery($this->Marker->video_id, $search);
                $result = Mediatag::$dbconn->query($query);

                $markers = $this->getVideoMarks($result);

                if (\count($markers) > 0) {
                    ++$this->FileIdx;

                    $markerArray[] = $markers;
                }
            }
        }
        $this->markerArray = $markerArray;

        return $this->markerArray;
    }
}
