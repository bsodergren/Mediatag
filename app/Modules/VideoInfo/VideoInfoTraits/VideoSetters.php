<?php

namespace Mediatag\Modules\VideoInfo\VideoInfoTraits;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\helpers\VideoCleaner;
use Mediatag\Modules\VideoInfo\helpers\VideoQuery;
use Mediatag\Modules\VideoInfo\helpers\VideoStrings;
use UTM\Utilities\DynamicProperty;
use UTM\Bundle\mysql\MysqliDb;

use function array_key_exists;
use function count;
use function sprintf;

trait VideoSetters
{
    public function save()
    {
        // utminfo(func_get_args());

        $this->VideoInfo['video_key'] = $this->video_key;
        $this->VideoInfo['library']   = __LIBRARY__;

        if (array_key_exists('duration', $this->VideoInfo)) {
            if ($this->VideoInfo['duration'] === null) {
                return false;
            }
        }
        if (array_key_exists('format', $this->VideoInfo)) {
            if ($this->VideoInfo['format'] === null) {
                return false;
            }
        }
        // // utmdump($this->VideoInfo);

        if (Storage::$DB->insert($this->VideoInfo, $this->VideoDataTable)) {
            // $this->returnText = '<comment>Updated</comment> ';//.$this->videoData;
            // utmdd(["ffdssd",$this->getVideoText(),$this->returnText]);
            return $this->getVideoText();
        }
    }

    public function updateVideoData()
    {
        // utminfo(func_get_args());

        $file_array = $this->getDbList();
        $this->getMessageLen($file_array);
        if (count($file_array) > 0) {
            $this->fileCount = count($file_array);
            Mediatag::$output->writeln('<info>Found ' . $this->fileCount . ' files</info>');

            // $this->maxLen = 0;

            foreach ($file_array as $key => $file) {
                if (file_exists($file)) {
                    $res = $this->getVideoInfo($key, $file);

                    if ($res !== false) {
                        if ($this->progressBar === false) {
                            Mediatag::$output->writeln($this->printNo($this->fileCount) . $this->getVideoText());
                            $this->fileCount--;
                        }
                        $this->progressBar = false;
                    }
                }
            }
        } else {
            Mediatag::$output->writeln('All ' . $this->thumbType . ' files are updated');
        }
    }

    public function saveVideoDetails()
    {
        // utminfo(func_get_args());

        return $this->save();
    }
}
