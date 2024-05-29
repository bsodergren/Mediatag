<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\VideoData\Data\Duration;
use Mediatag\Modules\VideoData\Data\preview\GifPreviewFiles;
use Mediatag\Modules\VideoData\Data\Thumbnail;
use Mediatag\Modules\VideoData\Data\VideoInfo;
use Mediatag\Modules\VideoData\Data\VideoTags;
use UTM\Utilities\Option;
use Nette\Utils\Arrays;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

class StorageDB extends Storage
{
    public $DbFileArray = [];

    public $input;

    /**
     * output.
     */
    public $output;

    public $videoData;

    public $file_array;

    public $video_string = [];

    public $video_file;

    public $video_path;

    public $video_key;

    public $video_name;

    public $FileNumber;

    public $RowBlock;

    public $headerBlock;

    public $thumb;

    public $vinfo;
    public $vtags;
    public $preview;
    public $duration;

    public $dbConn;
    public $progressbar;
    public $progressbar1;

    public $MultiIDX = 1;

    public function init($video_file)
    {
        $fs = new File($video_file);
        $this->videoData = $fs->get();
        $this->video_path = File::file($video_file, 'filepath');
        $this->video_key = File::file($video_file, 'videokey');
        $this->video_name = File::file($video_file, 'filename');
        $this->video_file = $video_file;

        return $this;
    }

    // end init()
    public function getDbFileList()
    {
        $fileListArray = [];
        $query = $this->queryBuilder('select', "CONCAT(fullpath,'/',filename) as file_name, video_key");
        $results = $this->query($query);
        foreach ($results as $key => $arr) {
            $fileListArray[$arr['video_key']] = $arr['file_name'];
        }
        $this->DbFileArray = $fileListArray;

        // foreach ($fileListArray as $k => $file) {
        //     $filesArray[] = $file;
        // }

        // utmdd([__METHOD__,$filesArray]);

        return $this->DbFileArray;
    }

    public function removeDBEntry()
    {
        $thumb = $this->getThumbnailPath();

        if (null !== $thumb) {
            $thumbnail = __INC_WEB_THUMB_ROOT__.$thumb;
            if (file_exists($thumbnail)) {
                //  unlink($thumbnail);
            }
        }

        //   foreach (__MYSQL_TRUNC_TABLES__ as $table) {

        $query = 'select id from '.__MYSQL_VIDEO_FILE__.' WHERE video_key = "'.$this->video_key.'" ';
        $result = $this->queryOne($query);

        $query = 'delete from '.__MYSQL_VIDEO_FILE__.' WHERE video_key = "'.$this->video_key.'" ';
        $this->query($query);

        $query = 'select playlist_id from '.__MYSQL_PLAYLIST_VIDEOS__.' WHERE playlist_video_id = "'.$result['id'].'" ';
        $pl_res = $this->queryOne($query);
        if (null !== $pl_res) {
            if (0 < \count($pl_res)) {
                $query = 'delete from '.__MYSQL_PLAYLIST_VIDEOS__.' WHERE playlist_video_id = "'.$result['id'].'" ';
                $this->query($query);

                $query = 'select * from '.__MYSQL_PLAYLIST_VIDEOS__.' WHERE playlist_id = "'.$pl_res['playlist_id'].'" ';
                $pl_result = $this->query($query);
                if (null !== $pl_result) {
                    if (0 == \count($pl_result)) {
                        $query = 'delete from '.__MYSQL_PLAYLIST_DATA__.' WHERE id = "'.$pl_res['playlist_id'].'" ';
                        $this->query($query);
                    }
                }
            }
        }
        // }
        // UTMLog::logNotice($results);
    }

    // public function addDBEntry($data)
    // {
    //     $id = $this->insert($data);
    //     if ($id) {
    //         Mediatag::$output->writeln('Video '.$index.' out of '.$total.' '.$this->video_name);
    //     }
    // }

    public function addDBArray($data)
    {
        $this->video_string = [];
        $vdata = [];
        Mediatag::$Display->BlockInfo = [];
        $this->MultiIDX = 1;
        $total = \count($data);
        foreach ($data as $k => $row) {
            // $VideoQuery[$row['video_key']][__MYSQL_VIDEO_FILE__] = $row;
            $vdata = ['video_file' => $row['fullpath'].'/'.$row['filename']];

            $this->updateDBEntry($row['video_key'], $vdata, Option::istrue('all'));
            if($this->progressbar !== null){
                $this->progressbar->advance();
            }
            $this->progressbar1->advance();

            //            $this->video_string[] = '<info>'.$this->MultiIDX.'</info> : Video <comment>'.$row['filename'].'</comment> added to db ';
            ++$this->MultiIDX;
        }
        $this->video_string[] = ' '.\PHP_EOL;
        //   $this->RowBlock->overwrite($this->video_string);
    }

    public static function getSubLibrary($video_path)
    {
        $sublibrary = null;
        $filesystem = new SFilesystem();
        $in_directory = $filesystem->makePathRelative($video_path, __PLEX_HOME__);
        preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);
        if (Arrays::contains(__CHANNELS__, $match[2])) {
            $sublibrary = $match[2];
        }

        return $sublibrary;
    }

    public function createDbEntry($video_file, $video_key)
    {
        $this->init($video_file);

        $data = [
            'video_key' => $video_key,
            'filename' => $this->video_name,
            'fullpath' => $this->video_path,
            'Library' => __LIBRARY__,
            'subLibrary' => self::getSubLibrary($this->video_path),
            'filesize' => filesize($video_file),
        ];

        $data['added'] = $this->dbConn->now();

        return $data;
    }

    public function getThumbnailPath()
    {
        $where = ["video_key = '".$this->video_key."'"];

        return $this->getValue($where, 'thumbnail');
    }

    public function UpdateFilePath()
    {
        $this->video_string = [];
        $this->init($this->video_file);
        $data = [
            'fullpath' => $this->video_path,
            'filename' => $this->video_name,
            'thumbnail' => null,
        ];
        $where = ['video_key' => $this->video_key];
        $this->video_string = [$this->video_name.' has been updated '];
        $this->update($data, $where);
    }

    public function updateDBEntry($key, $videoData, $all = true)
    {
        $video_file = $videoData['video_file'];
        $video_id = true;
        $exists = $this->videoExists($key);
        Mediatag::$Display->BlockInfo = ['No' => '<info>'.$this->MultiIDX.'</info>'];
        $videoBlockInfo = null;
        $action = '<comment>Updated</comment> ';
        if (null === $exists) {
            $data_array = $this->createDbEntry($video_file, $key);
            $video_id = $this->insert($data_array);
            if($video_id !== null) {

                $query = 'insert into '.__MYSQL_VIDEO_SEQUENCE__.' (seq_id,video_id,video_key,Library) values ';
                $query .= " (nextseq('".__LIBRARY__."'),".$video_id.",'".$key."','".__LIBRARY__."')";
                $this->query($query);

                $action = '<comment>Added</comment> ';
            } else {
                $action = '<error>Duplicate</error> ';
            }
        }

        Mediatag::$Display->BlockInfo['Video'] = $action.basename($video_file).' ';
        if($video_id !== null) {

            $this->vtags = new VideoTags();
            Mediatag::$Display->BlockInfo['MetaTags'] = $this->vtags->getVideoInfo($key, $video_file);
            $this->vinfo = new VideoInfo();
            Mediatag::$Display->BlockInfo['VideoInfo'] = $this->vinfo->getVideoInfo($key, $video_file);

            if (true === $all) {
                $this->thumb = new Thumbnail();
                Mediatag::$Display->BlockInfo['thumbnail'] = $this->thumb->getVideoInfo($key, $video_file);

                $this->duration = new Duration();
                Mediatag::$Display->BlockInfo['Duration'] = $this->duration->getVideoInfo($key, $video_file);

                $this->preview = new GifPreviewFiles();
                Mediatag::$Display->BlockInfo['Preview'] = $this->preview->getVideoInfo($key, $video_file);

            }
        }

        foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
            $value = trim($value);

            $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=yellow');
        }
        if (\is_array($videoBlockInfo)) {
            $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->writeln($videoBlockInfo);
            //  Mediatag::$Display->VideoInfoSection->writeln("");
        }
    }
}
