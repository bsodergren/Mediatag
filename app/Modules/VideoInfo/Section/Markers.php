<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\Section;

use Mediatag\Core\Mediatag;
use Mediatag\Utilities\Strings;
use Mediatag\Traits\MediaFFmpeg;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;

class Markers extends VideoInfo
{
    use MediaFFmpeg;
    public $video_key;

    public $video_file;

    public $video_name;

    public $video_path;

    public $resultCount;

    public $returnText;

    public $updatedText = '<comment>Updated ';
    public $newText     = '<fg=red>Wrote ';
    public $actionText  = 'Thumbnail</> ';

    public $VideoDataTable = __MYSQL_VIDEO_CHAPTER__;

    public $thumbType = 'markers';
    public $maxLen    = 75;

    public $thumbExt = '.jpg';
    public $thumbDir = __INC_WEB_CHAPTER_DIR__;

   

    // public function clean()
    // {
    //     // utminfo(func_get_args());

    //     $missing                                = [];
    //     [$dbList,$missing_file, $missing_thumb] = $this->getExistingList();
    //     $res                                    = Mediatag::$finder->Search(__INC_WEB_CHAPTER_DIR__.'/'.__LIBRARY__, '*.jpg');
    //     if (null === $res) {
    //         $res = [];
    //     }

    //     $missing = array_diff($res, $dbList);

    //     foreach ($res as $k => $file) {
    //         if (!array_search($file, $dbList)) {
    //             $videoFile = $this->thumbToVideo($file);
    //             if (file_exists($videoFile)) {
    //                 $fs        = new File($videoFile);
    //                 $videoData = $fs->get();
    //                 $this->get($videoData['video_key'], $videoFile);
    //                 Mediatag::$output->writeln($this->returnText.'</info>');
    //             }
    //         }
    //     }

    //     if (\count($missing) > 0) {
    //         foreach ($missing as $k => $file) {
    //             $videoFile = $this->thumbToVideo($file);
    //             if (!file_exists($videoFile)) {
    //                 $this->renameThumb($file, true);
    //                 Mediatag::$output->writeln('<comment>Deleting '.$file.' </comment>');
    //             }
    //         }
    //     }

    //     if (\count($missing_thumb) > 0) {
    //         foreach ($missing_thumb as $k => $file) {
    //             $query = 'update '.$this->VideoDataTable.' set thumbnail = null WHERE id = '.$k.'';

    //             $result = Mediatag::$dbconn->query($query);
    //             $file   = $this->thumbToVideo($file);

    //             Mediatag::$output->write('<comment>Changing '.$k.' to null, '.$file.' </comment>');

    //             if (file_exists($file)) {
    //                 $fs        = new File($file);
    //                 $videoData = $fs->get();
    //                 $this->get($videoData['video_key'], $file);
    //                 Mediatag::$output->writeln($this->returnText); // .'</info>');
    //             } else {
    //                 Mediatag::$output->writeln('');
    //             }
    //         }
    //     }
    //     Filesystem::prunedirs(__INC_WEB_CHAPTER_DIR__.'/'.__LIBRARY__);

    //     Mediatag::$output->writeln('<comment> All Clean </comment>');
    // }

    public function get($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = (string) $key;
        // utmdd($this->video_file,$this->video_key,$this->video_markers);
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $thumbnail = $this->getMarkerImg();

        return $thumbnail;
    }

    public function save()
    {
        // utminfo(func_get_args());

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
        // utminfo(func_get_args());

        $this->video_name = basename($this->video_file);
        $this->video_path = \dirname($this->video_file);

        foreach ($this->video_markers as $timeCode) {
            foreach ($timeCode as $id=>$time) {
                $img_name     = basename($this->video_name, '.mp4').'_'.$time.'.jpg';
                $img_name     = Strings::cleanFileName($img_name);
                $img_web_path = (new Filesystem())->makePathRelative($this->video_path, __PLEX_HOME__);
                $img_location = __INC_WEB_CHAPTER_DIR__.'/'.$img_web_path;
                $img_file     = $img_location.$img_name;
                $img_url_path = __INC_WEB_CHAPTER_DIR__.'/'.$img_web_path.$img_name;

                if (!file_exists($img_file)) {
                    $timeStamp = self::videoDuration($time);
                    (new Filesystem())->mkdir($img_location);
                    $this->ffmegCreateThumb($this->video_file, $img_file, $timeStamp);
                }

                $thumbnailImages[] = ['id'=>$id, 'markerThumbnail'=>$img_file];
            }
        }

        return $thumbnailImages;
    }

  


    public function getVideoInfo($key, $row)
    {
        // utminfo(func_get_args());

        $this->video_file    = $row['filename'];
        $this->video_markers = $row['timeCode'];
        $this->video_key     = $key;

        $this->VideoInfo = $this->getVideoDetails();

        // utmdd($this->VideoInfo);
        return $this->saveVideoDetails();
    }

    public function updateVideoData()
    {
        // utminfo(func_get_args());

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

    // public function getDbList()
    // {
    //     // utminfo(func_get_args());

    //     $file_array = [];

    //     $query  = $this->videoQuery();
    //     $result = Mediatag::$dbconn->query($query);

    //     foreach ($result as $_ => $row) {
    //         $video_key = $row['video_key'];
    //         if (!\array_key_exists($video_key, $file_array)) {
    //             // } else {
    //             $file_array[$video_key] = [
    //                 'filename' => $row['file_name'],
    //                 'timeCode' => []];
    //         }
    //         $file_array[$video_key]['timeCode'][] = [$row['id']=>$row['timeCode']];
    //         //  $file_array[$row['video_key']]['filename'][] = $row['timeCode'];
    //     }

    //     // $this->resultCount = \count($file_array);

    //     return $file_array;
    // }
}
