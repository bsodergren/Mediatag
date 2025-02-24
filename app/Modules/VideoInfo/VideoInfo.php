<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\VideoInfo\helpers\VideoQuery;
use Mediatag\Modules\VideoInfo\helpers\VideoCleaner;
use Mediatag\Modules\VideoInfo\helpers\VideoStrings;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;


class VideoInfo
{
    use VideoCleaner;
    use VideoQuery;
    use VideoStrings;
    public $video_key;

    public $video_file;
    public $video_id;
    public $returnText;
    public $updatedText = '<fg=green>Updated ';
    public $newText     = '<fg=red>Wrote ';
    public $resultCount;
    public $actionText = '';
    public $VideoInfo;
    public $fileCount;
    public $maxLen  = 40;
    public $fileLen = 0;

    public $thumbExt  = '.jpg';
    public $thumbDir  = __INC_WEB_THUMB_DIR__;
    public $thumbType = 'preview';

    public $progressBar = false;

    public $VideoDataTable;
    public $VideoFileTable = __MYSQL_VIDEO_FILE__;

    /**
     * Summary of getVideoDetails.
     *
     * @return array
     */
    public function getVideoDetails()
    {
        // utminfo(func_get_args());

        return $this->get($this->video_key, $this->video_file);
    }

    public function saveVideoDetails()
    {
        // utminfo(func_get_args());

        return $this->save();
    }

    public function getVideoInfo($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = $key;
        $exists           = Mediatag::$dbconn->videoExists($key, null, $this->VideoFileTable);
        if (null === $exists) {
            $data_array = Mediatag::$dbconn->createDbEntry($file, $key);
            Mediatag::$dbconn->insert($data_array);
        }

        $this->VideoInfo = $this->getVideoDetails();
utmdump($this->VideoInfo);
        return $this->saveVideoDetails();
    }

    public function updateVideoData()
    {
        // utminfo(func_get_args());

        $file_array = $this->getDbList();
        $this->getMessageLen($file_array);
        if (\count($file_array) > 0) {
            $this->fileCount = \count($file_array);
            Mediatag::$output->writeln('<info>Found '.$this->fileCount.' files</info>');

            // $this->maxLen = 0;

            foreach ($file_array as $key => $file) {
                if (file_exists($file)) {
                    $res = $this->getVideoInfo($key, $file);

                    if (false !== $res) {
                        if (false === $this->progressBar) {
                            Mediatag::$output->writeln($this->printNo($this->fileCount).$this->getVideoText());
                            --$this->fileCount;
                        }
                        $this->progressBar = false;
                    }
                }
            }
        } else {
            Mediatag::$output->writeln('All '.$this->thumbType.' files are updated');
        }
    }

   

    public function save()
    {
        // utminfo(func_get_args());

        $this->VideoInfo['video_key'] = $this->video_key;
        $this->VideoInfo['library']   = __LIBRARY__;

        if (\array_key_exists('duration', $this->VideoInfo)) {
            if (null === $this->VideoInfo['duration']) {
                return false;
            }
        }
        if (\array_key_exists('format', $this->VideoInfo)) {
            if (null === $this->VideoInfo['format']) {
                return false;
            }
        }
        // utmdump($this->VideoInfo);

        if (Mediatag::$dbconn->insert($this->VideoInfo, $this->VideoDataTable)) {
            // $this->returnText = '<comment>Updated</comment> ';//.$this->videoData;
            // utmdd(["ffdssd",$this->getVideoText(),$this->returnText]);
            return $this->getVideoText();
        }
    }

    public function getvideoId($key)
    {
        $this->VideoInfo = Mediatag::$dbconn->videoExists($key, null, $this->VideoFileTable);
        $this->video_id  = null;
        if (null === $this->VideoInfo) {
            return null;
        }
        $this->video_id = $this->VideoInfo['id'];

        return $this->video_id;
        // utmdd($exists);
    }

    public function clean()
    {
        $this->doClean();
        Filesystem::prunedirs($this->thumbDir.'/'.__LIBRARY__);
        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }
    public function clearDBValues()
    {
        $this->doClean(true);
        Filesystem::prunedirs($this->thumbDir.'/'.__LIBRARY__);
        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    

    public static function videoDuration($duration, $round = 1000)
    {
        if (0 == $round) {
            $round = 1;
        }
        // utminfo();
        $seconds = (int) round($duration / $round);
        $secs    = $seconds % 60;
        $hrs     = $seconds / 60;
        $hrs     = floor($hrs);
        $mins    = $hrs % 60;
        $hrs /= 60;

        return \sprintf('%02d:%02d:%02d.00', $hrs, $mins, $secs);
    }
}
