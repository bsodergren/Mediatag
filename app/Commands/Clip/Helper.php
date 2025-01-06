<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Traits\ffmpeg;
use Mediatag\Traits\ffmpegTransition;
use Mediatag\Traits\Translate;
use Mediatag\Utilities\Chooser;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait Helper
{
    use ffmpeg;
    use ffmpegTransition;

    use MarkerHelper;
    public $Marker;
    public $markerArray;

    public function timeCodetoSec($time)
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

        return $time;
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

    public function deleteClips()
    {
        Translate::$Class = __CLASS__;

        $directory  = $this->getClipDirectory(__CURRENT_DIRECTORY__, 0);
        $file_array = Mediatag::$finder->Search($directory, '*.mp4');

        $videos = \count($file_array);
        // $question = new Question(Translate::text('L__CLIP_ASK_CONTINUE'));
        // Mediatag::$output->writeln();

        $go = Chooser::changes(Translate::text('L__CLIP_VIDEO_COUNT', ['VID' => $videos]), 'yes', __LINE__);

        if (true === $go) {
            Mediatag::$output->writeln('Deleting '.$videos.' entrys in the DB');
            foreach ($file_array as $file) {
                Mediatag::$output->writeLn('<info> removing file '.basename($file).'</info>');
                Mediatag::$filesystem->remove($file);
                utmdump($file);
            }
        }
    }

    public function addMarker()
    {
        $time = Option::getValue('time');
        $name = Option::getValue('name', true);

        $video_id = (new Markers())->getvideoId(key($this->VideoList['file']));

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

    public function mergeClips()
    {
        $fileSearch = Option::getValue('merge', true);

        $name      = Option::getValue('name', true);
        $directory = $this->getClipDirectory(__CURRENT_DIRECTORY__, 0);
        if (null !== $fileSearch) {
            $search = '/.*_('.$name.')_\d+\.mp4/i';
        } else {
            $search = '*.mp4';
        }
        if (null === $name) {
            $name = 'Compilation';
        }
        $file_array = Mediatag::$finder->Search($directory, $search);
        if (null == $file_array) {
            Mediatag::$output->writeln('<comment> No Files Found</>');

            return false;
        }
        $current_file = '';
        $mod          = 0;
        $index        = 0;
        foreach ($file_array as $file) {
            preg_match('/([a-zA-Z0-9]+)_.*([0-9]+)(.mp4)/', $file, $output_array);
            if ($current_file != $output_array[1]) {
                $current_file = $output_array[1];
                $mod += $index;
                $index = 0;
            }
            $idx = $output_array[2] + $mod;

            $fileList[$idx] = $file;
            ++$index;
        }
        ksort($fileList);

        // foreach ($fileList as $line) {
        //     $strArray[] = "file '".$line."'";
        // }
        // $string   = implode("\n", $strArray);
        // $listFile = $this->setffmpegFilename($name);
        // Filesystem::write($listFile, $string, 0755);
        $ClipName       = $this->setClipFilename($name);
        // $this->progress = new MediaIndicator('one');

        // utmdd($file);

        $this->createCompilation($fileList, $ClipName, $name);
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
                //  utmdd($markers);

                if (\count($markers) > 0) {
                    ++$this->FileIdx;

                    $markerArray[] = $markers;
                }
            }
        }
        $this->markerArray = $markerArray;

        return $this->markerArray;
    }

    public function createClips()
    {
        $this->progress = new MediaIndicator('one');

       



        foreach ($this->markerArray as $i =>$fileRow) {
            foreach ($fileRow as $K =>$FILE) {
                $filename = $FILE['filename'];

                if (\count($FILE['markers']) > 0) {
                    // Mediatag::$output->writeln('<comment>'.$this->FileIdx--.'</> <fg=green>'.basename($filename).'</>');
                    foreach ($FILE['markers'] as $idx =>$marker) {
                        // $frame_json   = $this->ffmprobeGetFrames($filename, $marker['start'], $marker['end']);
                        // $this->frames = $frame_json['streams'][0]['nb_read_frames'];

                        $this->ffmpegCreateClip($filename, $marker, $idx);
                    }
                }
            }
        }
    }
}
