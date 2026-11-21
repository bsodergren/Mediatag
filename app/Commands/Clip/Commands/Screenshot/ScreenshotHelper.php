<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Screenshot;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Mediatag\Bundle\Grephp\Grephp;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Display\MediaIndicator;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\Screenshots;
use Mediatag\Modules\VideoInfo\Section\Thumbnail;
use Mediatag\Modules\VideoInfo\Section\VideoTags;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Mhor\MediaInfo\MediaInfo;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

use function array_key_exists;
use function count;
use function dirname;

use const PHP_EOL;

trait ScreenshotHelper
{
    use MediaFFmpeg;

    public $videoInfo;

    public $commandline;

    public $ScreenshotArray = [];

    private $FileIdx = 0;

    public function getScreenshotList()
    {
        $this->videoInfo        = new VideoInfo();
        $this->videoInfo->thumbType = 'screenshot';
        $this->videoInfo->VideoDataTable = \__MYSQL_VIDEO_SCREENSHOTS__;
        $this->videoInfo->thumbExt       = '.jpg';
        $this->videoInfo->thumbDir       = __INC_WEB_SCREENSHOT_DIR__;
        $this->videoInfo->ThumbnailField = 'ScreenshotFile';

        $screenShotArray = [];

        foreach ($this->VideoList['file'] as $key => $vidArray) {
            $key                = $vidArray['video_key'];
            $video_id           = $this->videoInfo->getvideoId($key);
            $query    = $this->videoInfo->videoQuery($video_id);
            $result   = Storage::$DB->query($query);
            if (\count($result) > 0) {
                $screenShotArray[$video_id] = ['filename' => $result[0]['filename'], 'timeCodes' => []];
                foreach ($result as $rkey => $row) {
                    $screenShotArray[$video_id]['timeCodes'][$row['id']] = $row['timeCode'];
                }
            }
        }
        $this->ScreenshotArray = $screenShotArray;
    }

    public function createScreenshotFile()
    {

        if (\count($this->ScreenshotArray) == 0) {
            Mediatag::$output->writeln('<comment> no  screenshots to be made </comment> <info>Already has chapters</info>');

            return false;
        }

        // utmdump([$this->video_file, $img_file, $time]);
        utmdump($this->ScreenshotArray);

        $ffmpeg = FFMpeg::create();
        foreach ($this->ScreenshotArray as $video_id => $fileRow) {

            $video_file = $fileRow['filename'];
            $video_timeCodes = $fileRow['timeCodes'];

            // $video_name = basename($video_file);
            $video_path = \dirname($video_file);
            $img_basename = basename($this->videoInfo->videoToThumb($video_file));
            $img_web_path = trim((new Filesystem())->makePathRelative($video_path, __PLEX_HOME__), '/');
            $img_location = __INC_WEB_SCREENSHOT_DIR__ . '/' . $img_web_path . \DIRECTORY_SEPARATOR;
            (new Filesystem())->mkdir($img_location);

            foreach ($video_timeCodes as $tcId => $tcRow) {
                $img_name         = str_replace(' ', '_', basename($img_basename, '.jpg')) . '-' . $tcId . '.jpg';
                $img_url_path     = __INC_WEB_SCREENSHOT_URL__ . '/' . $img_web_path . $img_name;
                $img_file         = $img_location . $img_name;

                $img_url_path = str_replace(__WEB_HOME__, '', $img_file);
                // $time     = VideoInfo::videoDuration($tcRow, 10);
                if (! file_exists($img_file)) {
                    $video = $ffmpeg->open($video_file);

                    $frame = $video->frame(TimeCode::fromSeconds($tcRow));
                    $frame->save($img_file);

                    Storage::$DB->update([
                        $this->videoInfo->ThumbnailField => $img_url_path,
                    ], [
                        'id' => $tcId], $this->videoInfo->VideoDataTable);
                }
            }
        }

    }
}
