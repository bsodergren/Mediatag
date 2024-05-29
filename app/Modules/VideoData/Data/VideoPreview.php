<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Core\Mediatag;
//use Intervention\Image\Image;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\VideoData;
use UTM\Utilities\Option;

class VideoPreview extends VideoData
{
    public $video_key;

    public $video_file;

    public $video_name;

    public $video_path;

    public $preview_path;

    public $previewName;



    public $VideoDataTable = __MYSQL_VIDEO_FILE__;

    public function getPreviewFiles()
    {

    }

    public function previewToVideo($file)
    {

    }

    public function videoToPreview($file)
    {

    }

    public function build_video_thumbnail()
    {

    }

    public function clean()
    {

        $missing = [];
        [$dbList,$missing_file, $missing_preview] = $this->getExistingList();
        $res = $this->getPreviewFiles();
        if($res === null) {
            $res = [];
        }
        $missing = array_diff($res, $dbList);

        if (\count($missing) > 0) {
            foreach ($missing as $k => $file) {
                $videoFile = $this->previewToVideo($file);
                if (! file_exists($videoFile)) {
                    unlink($file);
                    Mediatag::$output->writeln('<fg=red>UNLINK '.$file.'</>');

                    continue;
                }
            }
        }

        if (\count($missing_file) > 0) {
            foreach ($missing_file as $k => $file) {
                $query = 'update '.$this->VideoDataTable.' set preview = null WHERE id = '.$k.'';
                $result = Mediatag::$dbconn->query($query);
            }
        }

        if (\count($missing_preview) > 0) {
            foreach ($missing_preview as $k => $file) {
                $query = 'update '.$this->VideoDataTable.' set preview = null WHERE id = '.$k.'';

                $result = Mediatag::$dbconn->query($query);
                $file = $this->previewToVideo($file);

                Mediatag::$output->write('<comment>Changing '.$k.' to null, </comment>');

                if (file_exists($file)) {
                    $fs = new File($file);
                    $videoData = $fs->get();
                    $this->get($videoData['video_key'], $file);
                    Mediatag::$output->writeln($this->returnText); // .'</info>');
                } else {
                    Mediatag::$output->writeln('');
                }
            }
        }
        Filesystem::prunedirs(__INC_WEB_PREVIEW_DIR__.'/'.__LIBRARY__);

        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    public function get($key, $file)
    {
        $this->video_file = $file;
        $this->video_key = (string) $key;
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $preview = $this->BuildPreview();

        return ['preview' => $preview, 'video_key' => $this->video_key];
    }

    public function BuildPreview()
    {



        //$this->video_file = $this->video_file;
        $this->previewName = $this->videoToPreview($this->video_file);
        $this->video_name = basename($this->video_file);
        if(file_exists($this->previewName)) {
            $this->video_name = $this->video_name . " Exists";

            return str_replace(__INC_WEB_THUMB_ROOT__, '', $this->previewName);
            //   return false;
        }

        $this->preview_path = \dirname($this->previewName);
        (new FileSystem())->mkdir($this->preview_path);

        return $this->build_video_thumbnail();
        //utmdd($this->preview_path);



    }

    /**
     * getExistingList.
     */
    private function getExistingList(): array
    {
        $missing_thumb = [];
        $missing_mp4 = [];
        $query = "SELECT  CONCAT(fullpath,'/',filename) as file_name,id FROM ".$this->VideoDataTable.
        " WHERE Library = '".__LIBRARY__."' AND  preview is not null AND  fullpath like '".__CURRENT_DIRECTORY__."%'";
        $result = Mediatag::$dbconn->query($query);
        $dblist = [];
        foreach ($result as $_ => $row) {
            $thumb = $this->videoToPreview($row['file_name']);
            if (! file_exists($row['file_name'])) {
                $missing_mp4[$row['id']] = $thumb;

                continue;
            }

            if (! file_exists($thumb)) {
                $missing_thumb[$row['id']] = $row['file_name'];

                continue;
            }
            $dblist[$row['id']] = $thumb;
        }

        return [$dblist, $missing_mp4, $missing_thumb];
    }

    public function videoQuery()
    {
        $where = ' preview is null ';

        if (Option::istrue('update')) {
            $where = ' preview is not null ';
        }

        return "SELECT CONCAT(fullpath,'/',filename) as file_name, video_key FROM ".$this->VideoDataTable.
        " WHERE  Library = '".__LIBRARY__."' AND  ". $where . " AND  fullpath like '".__CURRENT_DIRECTORY__."%'";
    }
}
