<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip;

use Mediatag\Commands\Clip\Markers\Markers as MarkerHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\Data\Markers;
use Mediatag\Traits\ffmpeg;
use Mediatag\Traits\Translate;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use UTM\Utilities\Option;

trait Helper
{
    use ffmpeg;

    use MarkerHelper;
    public $Marker;
    public $markerArray;

    public $BarStyle = ['*----', '-*---', '--*--', '---*-', '----*', '---*-', '--*--', '-*---'];

    public function CreateNewIndicator()
    {
        $this->progress = null;
        $this->progress = new ProgressIndicator(Mediatag::$Display->BarSection1, 'normal', 50, $this->BarStyle, '🎉');
    }

    public function startIndicator($message)
    {
        $this->progress->start('<fg=bright-cyan>'.$message.'</>');
    }

    public function finishIndicator($message)
    {
        $this->progress->finish('<fg=green>'.$message.'</>');
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

        $filename = __CURRENT_DIRECTORY__.\DIRECTORY_SEPARATOR.'Compilation'.\DIRECTORY_SEPARATOR.$name.'.mp4';
        Filesystem::createDir(\dirname($filename));

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

        if (Option::istrue('yes')) {
            $go     = true;
            $answer = 'y';
        } else {
            Mediatag::$output->writeln(Translate::text('L__CLIP_VIDEO_COUNT', ['VID' => $videos]));
            $ask      = new QuestionHelper();
            $question = new Question(Translate::text('L__CLIP_ASK_CONTINUE'));

            $answer = $ask->ask(Mediatag::$input, Mediatag::$output, $question);
        }
        switch ($answer) {
            case 'y':
                $go = true;

                break;

            case 'Y':
                $go = true;

                break;

            default:
                $go = false;

                break;
        }

        if (true == $go) {
            Mediatag::$output->writeln('Deleting '.$videos.' entrys in the DB');

            foreach ($file_array as $file) {
                unlink($file);

                utmdump($file);
            }
        }

        utmdd(
            ''
        );
    }

    public function getClips()
    {
        $name      = Option::getValue('convert', true);
        
        $directory = $this->getClipDirectory(__CURRENT_DIRECTORY__, 0);
        if($name !== null) {
            $search = '/.*_('.$name.')_\d+\.mp4/i';
        } else { 
            $search = "*.mp4";
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
        foreach ($fileList as $line) {
            $strArray[] = "file '".$line."'";
        }
        $string   = implode("\n", $strArray);
        $listFile = $this->setffmpegFilename($name);
        Filesystem::write($listFile, $string, 0755);
        $ClipName = $this->setClipFilename($name);
        $this->CreateNewIndicator();

        $this->createCompilation($listFile, $ClipName);
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
                ++$this->FileIdx;
                $query  = $this->Marker->videoQuery($this->Marker->video_id, $search);
                $result = Mediatag::$dbconn->query($query);

                $markers = $this->getVideoMarks($result);
                //  utmdd($markers);
                if (\count($markers) > 0) {
                    $markerArray[] = $markers;
                }
            }
        }
        $this->markerArray = $markerArray;

        return $this->markerArray;
    }

    public function createClip()
    {
        $this->CreateNewIndicator();
        foreach ($this->markerArray as $i =>$fileRow) {
            foreach ($fileRow as $K =>$FILE) {
                $filename = $FILE['filename'];

                if (\count($FILE['markers']) > 0) {
                    Mediatag::$output->writeln('<comment>'.$this->FileIdx--.'</> <fg=green>'.basename($filename).'</>');
                    foreach ($FILE['markers'] as $idx =>$marker) {
                        $frame_json   = $this->ffmprobeGetFrames($filename, $marker['start'], $marker['end']);
                        $this->frames = $frame_json['streams'][0]['nb_read_frames'];

                        $this->ffmpegCreateClip($filename, $marker, $idx);
                    }
                }
            }
        }
    }
}
