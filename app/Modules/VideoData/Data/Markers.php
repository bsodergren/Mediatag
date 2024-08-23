<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoData\Data;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Traits\ffmpeg;
use Mediatag\Utilities\Strings;
use Mhor\MediaInfo\MediaInfo;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;
use UTM\Utilities\Option;

class Markers extends VideoData
{
    use ffmpeg;

    public $video_key;

    public $video_file;

    public $video_name;

    public $video_path;

    public $resultCount;

    public $returnText;

    private $updatedText   = "<comment>Updated ";
    private $newText       = "<fg=red>Wrote ";
    private $actionText    = 'Thumbnail</> ';

    public $VideoDataTable = __MYSQL_VIDEO_CHAPTER__;


    private function videoDuration($duration)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $seconds = (int) round($duration);
        $secs    = $seconds % 60;
        $hrs     = $seconds / 60;
        $hrs     = floor($hrs);
        $mins    = $hrs     % 60;
        $hrs /= 60;

        return sprintf('%02d:%02d:%02d', $hrs, $mins, $secs);
    }

    public function clean()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $missing                                = [];
        [$dbList,$missing_file, $missing_thumb] = $this->getExistingList();
        $res                                    = Mediatag::$finder->Search(__INC_WEB_CHAPTER_DIR__ . '/' . __LIBRARY__, '*.jpg');
        if ($res === null) {
            $res = [];
        }

        $missing                                = array_diff($res, $dbList);


        foreach ($res as $k => $file) {
            if (! array_search($file, $dbList)) {
                $videoFile = self::thumbToVideo($file);
                if (file_exists($videoFile)) {
                    $fs        = new File($videoFile);
                    $videoData = $fs->get();
                    $this->get($videoData['video_key'], $videoFile);
                    Mediatag::$output->writeln($this->returnText . '</info>');
                }
            }
        }

        if (\count($missing) > 0) {
            foreach ($missing as $k => $file) {
                $videoFile = self::thumbToVideo($file);
                if (! file_exists($videoFile)) {
                    $this->renameThumb($file, true);
                    Mediatag::$output->writeln('<comment>Deleting ' . $file . ' </comment>');
                }
            }
        }

        if (\count($missing_thumb) > 0) {
            foreach ($missing_thumb as $k => $file) {
                $query  = 'update ' . $this->VideoDataTable . ' set thumbnail = null WHERE id = ' . $k . '';

                $result = Mediatag::$dbconn->query($query);
                $file   = $this->thumbToVideo($file);

                Mediatag::$output->write('<comment>Changing ' . $k . ' to null, ' . $file . ' </comment>');

                if (file_exists($file)) {
                    $fs        = new File($file);
                    $videoData = $fs->get();
                    $this->get($videoData['video_key'], $file);
                    Mediatag::$output->writeln($this->returnText); // .'</info>');
                } else {
                    Mediatag::$output->writeln('');
                }
            }
        }
        Filesystem::prunedirs(__INC_WEB_CHAPTER_DIR__ . '/' . __LIBRARY__);

        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    public function getText()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        // return $this->returnText .basename($this->video_name, '.mp4').'.jpg';// .' for '.basename($this->video_file);

    }

    public function get($key, $file)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->video_file = $file;
        $this->video_key  = (string) $key;
        // utmdd($this->video_file,$this->video_key,$this->video_markers);
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $thumbnail        = $this->getMarkerImg();

        return $thumbnail;
    }

    public function save()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        // $this->VideoInfo['video_key'] = $this->video_key;
        // $this->VideoInfo['library'] = __LIBRARY__;
        foreach ($this->VideoInfo as $data) {
            $id = $data['id'];
            unset($data['id']);
            Mediatag::$dbconn->update($data, ['id'=>$id], $this->VideoDataTable);
        }
        $this->returnText = '<comment>Updated</comment> ';

        // return $this->getVideoText();

    }

    public function getMarkerImg()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->video_name = basename($this->video_file);
        $this->video_path = \dirname($this->video_file);

        foreach ($this->video_markers as $timeCode) {
            foreach ($timeCode as $id=>$time) {
                $img_name          = basename($this->video_name, '.mp4') . '_' . $time . '.jpg';
                $img_name          = Strings::cleanFileName($img_name);
                $img_web_path      = (new Filesystem())->makePathRelative($this->video_path, __PLEX_HOME__);
                $img_location      = __INC_WEB_CHAPTER_DIR__ . '/' . $img_web_path;
                $img_file          = $img_location . $img_name;
                $img_url_path      = __INC_WEB_CHAPTER_DIR__ . '/' . $img_web_path . $img_name;

                if (! file_exists($img_file)) {
                    $timeStamp = $this->videoDuration($time);
                    (new FileSystem())->mkdir($img_location);
                    $this->ffmegCreateThumb($this->video_file, $img_file, $timeStamp);
                }

                $thumbnailImages[] = ["id"=>$id,'markerThumbnail'=>$img_file];
            }
        }

        return $thumbnailImages;
    }

    public function videoQuery()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $fields = " CONCAT(f.fullpath,'/',f.filename) as file_name, f.video_key, vm.timeCode,vm.id ";
        $where  = ' vm.markerThumbnail is null ';

        if (Option::istrue('update')) {
            $where = ' vm.markerThumbnail is not null ';
        }

        $where  = $where . ' AND f.id = vm.video_id ';

        return "SELECT " . $fields . " FROM

         " . $this->VideoDataTable . " vm,
         " . __MYSQL_VIDEO_FILE__ . " f WHERE " . $where;
    }


    public function clearQuery($key = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $where = '';
        if (null !== $key) {
            $exists = Mediatag::$dbconn->videoExists($key, null, $this->VideoDataTable);
            if (null !== $exists) {
                $where = "AND video_key = '" . $key . "'";
            }
        }

        return 'update ' . $this->VideoDataTable . ' set thumbnail = null WHERE Library = "' . __LIBRARY__ . '"';
    }

    /**
     * getExistingList.
     */
    private function getExistingList(): array
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $missing_thumb = [];
        $missing_mp4   = [];
        $query         = "SELECT  CONCAT(fullpath,'/',filename) as file_name,id FROM " . $this->VideoDataTable . " WHERE Library = '" . __LIBRARY__ . "' AND  thumbnail is not null";

        utmdd($query);
        $result        = Mediatag::$dbconn->query($query);
        $dblist        = [];
        foreach ($result as $_ => $row) {
            $thumb              = self::videoToThumb($row['file_name']);
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

    public static function videoToThumb($file)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        return str_replace('.mp4', '.jpg', __INC_WEB_CHAPTER_DIR__ . str_replace(__PLEX_HOME__, '', $file));
    }

    public static function thumbToVideo($file)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        return str_replace('.jpg', '.mp4', __PLEX_HOME__ . str_replace(__INC_WEB_CHAPTER_DIR__, '', $file));
    }

    public function renameThumb($file, $delete = false)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (true === $delete) {
            unlink($file);

            return 0;
        }
        $newFile = str_replace('thumbnails', 'backup', $file);
        $path    = \dirname($newFile);

        if (! is_dir($path)) {
            (new SFileSystem())->mkdir($path);
        }

        (new SFileSystem())->rename($file, $newFile);
    }



    public function getVideoInfo($key, $row)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $this->video_file    = $row['filename'];
        $this->video_markers = $row['timeCode'];
        $this->video_key     = $key;

        $this->VideoInfo     = $this->getVideoDetails();
        // utmdd($this->VideoInfo);
        return $this->saveVideoDetails();
    }

    public function updateVideoData()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $file_array = $this->getDbList();
        if (\count($file_array) > 0) {
            foreach ($file_array as $key => $row) {
                $file = $row['filename'];
                if (file_exists($file)) {
                    $this->getVideoInfo($key, $row);
                    // if (! Option::istrue('all')) {
                    //     $int = $this->resultCount--;
                    //     $int = str_pad($int, 4, ' ', \STR_PAD_LEFT);
                    //     Mediatag::$output->writeln('<info>'.$int.'</info> : '.$this->getVideoText());
                    // }
                }
            }
        }
    }
    public function getDbList()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $file_array = [];

        $query      = $this->videoQuery();
        $result     = Mediatag::$dbconn->query($query);

        //
        foreach ($result as $_ => $row) {
            $video_key = $row['video_key'];
            if (!array_key_exists($video_key, $file_array)) {

                // } else {
                $file_array[$video_key] = [
                    'filename' => $row['file_name'],
                    'timeCode' => []];



            }
            array_push($file_array[$video_key]['timeCode'], [$row['id']=>$row['timeCode']]);
            //  $file_array[$row['video_key']]['filename'][] = $row['timeCode'];
        }

        // $this->resultCount = \count($file_array);

        return $file_array;
    }


}
