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

class Thumbnail extends VideoData
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

    public $VideoDataTable = __MYSQL_VIDEO_FILE__;

    public function clean()
    {
        utminfo();

        $missing                                = [];
        [$dbList,$missing_file, $missing_thumb] = $this->getExistingList();
        $res                                    = Mediatag::$finder->Search(__INC_WEB_THUMB_DIR__ . '/' . __LIBRARY__, '*.jpg');
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
                    $this->renameThumb($file, false);
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
        Filesystem::prunedirs(__INC_WEB_THUMB_DIR__ . '/' . __LIBRARY__);

        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    public function getText()
    {
        utminfo();

        return $this->returnText . basename($this->video_name, '.mp4') . '.jpg';// .' for '.basename($this->video_file);

    }

    public function get($key, $file)
    {
        utminfo();

        $this->video_file = $file;
        $this->video_key  = (string) $key;
        //        $VideoData             = new VideoData();
        //        $VideoData->video_file = $this->video_file;
        $thumbnail        = $this->getThumbImg();

        return ['thumbnail' => $thumbnail, 'video_key' => $this->video_key];
    }

    public function getThumbImg()
    {
        utminfo();

        $this->video_name = basename($this->video_file);
        $this->video_path = \dirname($this->video_file);

        $img_name         = basename($this->video_name, '.mp4') . '.jpg';
        $img_name         = Strings::cleanFileName($img_name);
        $img_web_path     = (new Filesystem())->makePathRelative($this->video_path, __PLEX_HOME__);
        $img_location     = __INC_WEB_THUMB_DIR__ . '/' . $img_web_path;
        $img_file         = $img_location . $img_name;
        $img_url_path     = __INC_WEB_THUMB_URL__ . '/' . $img_web_path . $img_name;
        $action           = $this->updatedText;
        $type             = $this->actionText;
        if (! file_exists($img_file)) {
            $mediaInfo          = new MediaInfo();
            $mediaInfoContainer = $mediaInfo->getInfo($this->video_file);
            $videos             = $mediaInfoContainer->getVideos();

            foreach ($videos as $video) {
                if (
                    null !== $video->get('source_duration')
                    && \array_key_exists('0', $video->get('source_duration'))
                ) {
                    $duration = (string) $video->get('source_duration')[0];
                } else {
                    $duration = (string) $video->get('duration');
                }
            }

            $time               = '00:01:00.00';

            if ((int) $duration < 70000) {
                $time = '00:00:15.00';
            }
            if ((int) $duration < 16000) {
                $time = '00:00:05.00';
            }

            if ((int) $duration < 5000) {
                $time = '00:00:02.00';
            }

            (new FileSystem())->mkdir($img_location);
            $this->ffmegCreateThumb($this->video_file, $img_file, $time);
            $action             = $this->newText;

        }

        $this->returnText = $action . $type;

        return $img_url_path;
    }

    public function videoQuery()
    {
        utminfo();

        $where = ' thumbnail is null ';

        if (Option::istrue('update')) {
            $where = ' thumbnail is not null ';
        }

        $where = $where . ' AND fullpath like \'' . __CURRENT_DIRECTORY__ . '%\' ';

        $query = "SELECT CONCAT(fullpath,'/',filename) as file_name, video_key FROM
         " . $this->VideoDataTable . " WHERE  Library = '" . __LIBRARY__ . "' AND  " . $where;


        return $query;
    }

    public function clearQuery($key = null)
    {
        utminfo();

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
        utminfo();

        $missing_thumb = [];
        $missing_mp4   = [];
        $query         = "SELECT  CONCAT(fullpath,'/',filename) as file_name,id FROM " . $this->VideoDataTable . " WHERE Library = '" . __LIBRARY__ . "' AND  thumbnail is not null";

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
        utminfo();

        return str_replace('.mp4', '.jpg', __INC_WEB_THUMB_DIR__ . str_replace(__PLEX_HOME__, '', $file));
    }

    public static function thumbToVideo($file)
    {
        utminfo();

        return str_replace('.jpg', '.mp4', __PLEX_HOME__ . str_replace(__INC_WEB_THUMB_DIR__, '', $file));
    }

    public function renameThumb($file, $delete = false)
    {
        utminfo();

        if (true === $delete) {
            unlink($file);

            return 0;
        }
        $newFile = str_replace('thumbnails', 'backup', $file);
        $path    = \dirname($newFile);

        if (! is_dir($path)) {
            (new SFileSystem())->mkdir($path);
        }

        (new SFileSystem())->rename($file, $newFile, true);
    }
}
