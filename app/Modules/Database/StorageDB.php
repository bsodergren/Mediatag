<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Database;

use const PHP_EOL;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\VideoInfo\Section\preview\GifPreviewFiles;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoPreview;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mhor\MediaInfo\Attribute\Duration;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem as nFilesystem;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function is_array;

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
        // utminfo(func_get_args());

        $fs               = new File($video_file);
        $this->videoData  = $fs->get();
        $this->video_path = File::file($video_file, 'filepath');
        $this->video_key  = File::file($video_file, 'videokey');
        $this->video_name = File::file($video_file, 'filename');
        $this->video_file = $video_file;

        return $this;
    }

    public function getAllDbFiles()
    {
        $query = $this->queryBuilder(
            'select',
            "CONCAT(fullpath,'/',filename) as file_name,fullpath, video_key",
            false,
            true
        );
        $results       = $this->query($query);
        $fileListArray = [];

        foreach ($results as $key => $arr) {
            if ($arr['fullpath'] === null) {
                continue;
            }
            $fileListArray[$arr['video_key']] = $arr['file_name'];
        }

        return $fileListArray;
    }

    // end init()
    public function getDbFileList()
    {
        // utminfo(func_get_args());

        $this->delete(__MYSQL_VIDEO_FILE__, ['fullpath', 'is null']);
        $fileListArray = [];

        $query   = $this->queryBuilder('select', "CONCAT(fullpath,'/',filename) as file_name,fullpath, video_key");
        $results = $this->query($query);
        foreach ($results as $key => $arr) {
            if ($arr['fullpath'] === null) {
                continue;
            }
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
        // utminfo(func_get_args());

        $thumb = $this->getThumbnailPath();

        if ($thumb !== null) {
            $thumbnail = __INC_WEB_THUMB_ROOT__ . $thumb;
            if (file_exists($thumbnail)) {
                //  unlink($thumbnail);
            }
        }

        //   foreach (__MYSQL_TRUNC_TABLES__ as $table) {

        $query  = 'select id from ' . __MYSQL_VIDEO_FILE__ . ' WHERE video_key = "' . $this->video_key . '" ';
        $result = $this->queryOne($query);

        $query = 'delete from ' . __MYSQL_VIDEO_FILE__ . ' WHERE video_key = "' . $this->video_key . '" ';
        $this->query($query);

        $query  = 'select playlist_id from ' . __MYSQL_PLAYLIST_VIDEOS__ . ' WHERE playlist_video_id = "' . $result['id'] . '" ';
        $pl_res = $this->queryOne($query);
        if ($pl_res !== null) {
            if (count($pl_res) > 0) {
                $query = 'delete from ' . __MYSQL_PLAYLIST_VIDEOS__ . ' WHERE playlist_video_id = "' . $result['id'] . '" ';
                $this->query($query);

                $query     = 'select * from ' . __MYSQL_PLAYLIST_VIDEOS__ . ' WHERE playlist_id = "' . $pl_res['playlist_id'] . '" ';
                $pl_result = $this->query($query);
                if ($pl_result !== null) {
                    if (count($pl_result) == 0) {
                        $query = 'delete from ' . __MYSQL_PLAYLIST_DATA__ . ' WHERE id = "' . $pl_res['playlist_id'] . '" ';
                        $this->query($query);
                    }
                }
            }
        }
        // }
        // // UTMlog::logNotice($results);
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
        // utminfo(func_get_args());

        $this->video_string           = [];
        $vdata                        = [];
        Mediatag::$Display->BlockInfo = [];
        // $this->MultiIDX               = 1;
        $total = count($data);
        // utmdd($this->MultiIDX );
        foreach ($data as $k => $row) {
            // $VideoQuery[$row['video_key']][__MYSQL_VIDEO_FILE__] = $row;
            $vdata = ['video_file' => $row['fullpath'] . '/' . $row['filename']];

            $this->updateDBEntry($row['video_key'], $vdata, Option::istrue('all'));
            if ($this->progressbar !== null) {
                $this->progressbar->advance();
            }
            $this->progressbar1->advance();

            //            $this->video_string[] = '<info>'.$this->MultiIDX.'</info> : Video <comment>'.$row['filename'].'</comment> added to db ';
            $this->MultiIDX--;
        }
        $this->video_string[] = ' ' . PHP_EOL;
        //   $this->RowBlock->overwrite($this->video_string);
    }

    public static function getSubLibrary($video_path)
    {
        // utminfo(func_get_args());

        $sublibrary   = null;
        $filesystem   = new Filesystem;
        $in_directory = $filesystem->makePathRelative($video_path, __PLEX_HOME__);
        preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);
        if (array_key_exists(2, $match)) {
            if (Arrays::contains(__CHANNELS__, $match[2])) {
                $sublibrary = $match[2];
            }
        }

        return $sublibrary;
    }

    private function getStudioPath($video_path)
    {
        if (File::isPornhubfile($this->video_file) == false) {
            return null;
        }
        $filesystem = new Filesystem;

        $in_directory = $filesystem->makePathRelative(
            $video_path,
            __PLEX_HOME__ . DIRECTORY_SEPARATOR . __LIBRARY__,
        );
        preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);

        return $match[2];
    }

    public function createDbEntry($video_file, $video_key)
    {
        // utminfo(func_get_args());

        $this->init($video_file);

        $data = [
            'video_key'   => $video_key,
            'filename'    => $this->video_name,
            // 'fullpath'   => $filesystem->makePathRelative($this->video_path, __PLEX_HOME__),
            'fullpath'    => $this->video_path,
            'studio_path' => $this->getStudioPath($this->video_path),
            'Library'     => __LIBRARY__,
            'subLibrary'  => self::getSubLibrary($this->video_path),
            'filesize'    => filesize($video_file),
        ];

        $data['added'] = $this->dbConn->now();

        return $data;
    }

    public function getThumbnailPath()
    {
        // utminfo(func_get_args());

        $where = [
            'video_key' => [$this->video_key(), '='],
        ];

        return $this->getValue($where, 'thumbnail');
    }

    public function UpdateFilePath($file)
    {
        // utminfo(func_get_args());
        Mediatag::$Display->BlockInfo = ['No' => '<info>' . $this->MultiIDX . '</info>'];
        $action                       = '<comment>No Changes</comment> ';

        $data               = [];
        $this->video_string = [];
        $this->init($file);

        $exists = $this->videoExists($this->video_key);
        if ($exists !== null) {
            $video_path   = nFileSystem::normalizePath($this->video_path);
            $current_path = nFileSystem::normalizePath($exists['fullpath']);

            if ($current_path != $video_path) {
                $action                = '<comment>File was Moved</comment> ';
                $exists['studio_path'] = null;
                $data                  = [
                    'fullpath' => $video_path,
                    'filename' => $this->video_name,
                ];

                $data['thumbnail'] = null;
                $data['preview']   = null;

                if ($exists['thumbnail'] != null) {
                    $orig_thumb = __WEB_HOME__ . $exists['thumbnail'];
                    Mediatag::$output->writeln('fasdsfda' . file_exists($orig_thumb));

                    if (file_exists($orig_thumb)) {
                        $img_name = (new thumbnail)->videoToThumb($this->video_file);

                        $path = dirname($img_name);

                        if (! is_dir($path)) {
                            (new Filesystem)->mkdir($path);
                        }

                        (new Filesystem)->rename($orig_thumb, $img_name, true);
                        $img_name          = str_replace(__INC_WEB_THUMB_ROOT__, '', $img_name);
                        $data['thumbnail'] = $img_name;
                    }
                }

                if ($exists['preview'] != null) {
                    $orig_prev = __WEB_HOME__ . '/' . $exists['preview'];
                    if (file_exists($orig_prev)) {
                        $img_name = (new VideoPreview)->videoToThumb($this->video_file);
                        $path     = dirname($img_name);

                        if (! is_dir($path)) {
                            (new Filesystem)->mkdir($path);
                        }
                        (new Filesystem)->rename($orig_prev, $img_name, true);
                        $data['preview'] = str_replace(__INC_WEB_THUMB_ROOT__, '', $img_name);
                    }
                }
            }

            if ($exists['studio_path'] == null) {
                if (count($data) < 1) {
                    $action = '<comment>Studio Path was Added</comment> ';
                }

                $data['studio_path'] = $this->getStudioPath($video_path);
            }
            if (count($data) > 0) {
                $where = ['video_key' => $this->video_key];
                $this->update($data, $where);

                Mediatag::$Display->BlockInfo['Video'] = $action . basename($file) . ' ';

                foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
                    $value = trim($value);

                    $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=yellow');
                }
                if (is_array($videoBlockInfo)) {
                    $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
                    Mediatag::$Display->VideoInfoSection->writeln($videoBlockInfo);
                    Mediatag::$Display->VideoInfoSection->writeln('');
                }
            }
        }

        // Mediatag::$Display->VideoInfoSection->writeln('');
        // $o = (new VideoTags)->getVideoInfo($this->video_key, $this->video_file);
        // utmdd($o);
    }

    public function updateDBEntry($key, $videoData, $all = true)
    {
        // utminfo(func_get_args());

        $video_file                   = $videoData['video_file'];
        $video_id                     = true;
        $exists                       = $this->videoExists($key);
        Mediatag::$Display->BlockInfo = ['No' => '<info>' . $this->MultiIDX . '</info>'];
        $videoBlockInfo               = null;
        $action                       = '<comment>Updated</comment> ';

        $ret = $this->queryOne('select name from sequence where name = "' . __LIBRARY__ . '" limit 1');

        if ($exists === null) {
            // utminfo(func_get_args());

            if ($ret === null) {
                $query = 'INSERT INTO `sequence` (`name`, `increment`, `min_value`, `max_value`, `cur_value`, `cycle`)';
                $query .= " VALUES ('" . __LIBRARY__ . "', '1', '1', '9223372036854775807', '1', '0')";

                $this->query($query);
                unset($query);
            }

            $data_array = $this->createDbEntry($video_file, $key);
            $video_id   = $this->insert($data_array);
            if ($video_id !== null) {
                $query = 'insert into ' . __MYSQL_VIDEO_SEQUENCE__ . ' (seq_id,video_id,video_key,Library) values ';
                $query .= " (nextseq('" . __LIBRARY__ . "')," . $video_id . ",'" . $key . "','" . __LIBRARY__ . "')";
                $this->query($query);

                $action = '<comment>Added</comment> ';
            } else {
                $action = '<error>Duplicate</error> ';
            }
        }

        Mediatag::$Display->BlockInfo['Video'] = $action . basename($video_file) . ' ';
        if ($video_id !== null) {
            // $this->vtags = new VideoTags();
            Mediatag::$Display->BlockInfo['MetaTags'] = (new VideoTags)->getVideoInfo($key, $video_file);
            // $this->vinfo = new VideoInfo();
            //
            if ($all === true) {
                // $this->thumb = new Thumbnail();
                Mediatag::$Display->BlockInfo['thumbnail'] = (new Thumbnail)->getVideoInfo($key, $video_file);

                Mediatag::$Display->BlockInfo['VideoInfo'] = (new VideoInfo)->getVideoInfo($key, $video_file);

                // $this->duration = new Duration();
                // Mediatag::$Display->BlockInfo['Duration']  = (new Duration())->getVideoInfo($key, $video_file);

                // $this->preview = new GifPreviewFiles();
                Mediatag::$Display->BlockInfo['Preview'] = (new GifPreviewFiles)->getVideoInfo($key, $video_file);
            }
        }

        foreach (Mediatag::$Display->BlockInfo as $tag => $value) {
            $value = trim($value);

            $videoBlockInfo[] = Mediatag::$Display->formatTagLine($tag, $value, 'fg=yellow');
        }
        if (is_array($videoBlockInfo)) {
            $videoBlockInfo = Mediatag::$Display->sortBlocks($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->writeln($videoBlockInfo);
            Mediatag::$Display->VideoInfoSection->writeln('');
        }
    }
}
