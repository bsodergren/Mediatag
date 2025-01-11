<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Merge;

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

trait MergeHelper
{


    public function mergeClips()
    {
        $fileSearch = Option::getValue('search', true);

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
            preg_match('/([a-zA-Z0-9-_]+)_([a-zA-Z0-9].*)_([0-9]+)(.mp4)/', $file, $output_array);
            if ($current_file != $output_array[1]) {
                $current_file = $output_array[1];
                $mod += $index;
                $index = 0;
            }
            $idx = $output_array[3] + $mod;

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


}
