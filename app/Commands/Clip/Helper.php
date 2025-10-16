<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use const DIRECTORY_SEPARATOR;

use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Traits\ffmpegTransition;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Utilities\Chooser;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;

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
        if (array_key_exists(1, $pcs)) {
            $minutes = $pcs[1] * 60;
        }
        if (array_key_exists(2, $pcs)) {
            $hours = $pcs[2] * 60 * 60;
        }

        $time = ($seconds + $minutes + $hours);

        return $time + $mod;
    }

    public function getClipDirectory($filename, $level = 1)
    {
        $outputFile = str_replace('/XXX', '/XXX/Clips', $filename);
        if ($level == 0) {
            return $outputFile;
        }

        return dirname($outputFile, $level);
    }

    public function getClipFilename($filename)
    {
        return $this->getClipDirectory($filename) . DIRECTORY_SEPARATOR . basename($filename);
    }

    public function setClipFilename($name)
    {
        $name = str_replace(' ', '_', $name);

        $filename = __LIBRARY_HOME__ . DIRECTORY_SEPARATOR . 'Home Videos' . DIRECTORY_SEPARATOR . 'Compilation' . DIRECTORY_SEPARATOR . $name . '.mp4';
        Filesystem::createDir(dirname($filename));

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
        return $this->getClipDirectory(__CURRENT_DIRECTORY__, 0) . DIRECTORY_SEPARATOR . $name . '.txt';
    }

    public function getfileList()
    {
        $markerArray   = [];
        $this->FileIdx = 0;

        $search = Option::getValue('clip', true);

        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $this->Marker = new Markers;

            $this->Marker->getvideoId($key);

            if ($this->Marker->video_id !== null) {
                $query = $this->Marker->videoQuery($this->Marker->video_id, $search);

                $result  = Mediatag::$dbconn->query($query);
                $markers = $this->getVideoMarks($result);

                if (count($markers) > 0) {
                    $this->FileIdx++;

                    $markerArray[] = $markers;
                }
            }
        }
        $this->markerArray = $markerArray;

        return $this->markerArray;
    }

    public function backupOrigFile($OriginalName, $NewName, $directory)
    {
        utmdump(__METHOD__);
        $file_path       = dirname($OriginalName);
        $backup_filepath = str_replace('XXX/', 'XXX/' . $directory . '/', $file_path);

  utmdump($backup_filepath);

        if (! Mediatag::$filesystem->exists($backup_filepath)) {
            Mediatag::$filesystem->mkdir($backup_filepath);
        }
        $backup_filename = $backup_filepath . '/' . basename($OriginalName);
        utmdump($backup_filename);
        //$outputFile      = str_replace('.mp4', '_chapters.mp4', $OriginalName);

        Filesystem::renameFile($OriginalName, $backup_filename);
                utmdump($NewName);

        Filesystem::renameFile($NewName, $OriginalName);
    }
}
