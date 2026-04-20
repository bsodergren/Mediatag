<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\helpers\VideoCleaner;
use Mediatag\Modules\VideoInfo\helpers\VideoQuery;
use Mediatag\Modules\VideoInfo\helpers\VideoStrings;
use Mediatag\Modules\VideoInfo\VideoInfoTraits\VideoGetters;
use Mediatag\Modules\VideoInfo\VideoInfoTraits\VideoSetters;
use Mediatag\Traits\DynamicProperty;

use function array_key_exists;
use function count;
use function sprintf;

class VideoInfo
{
    use DynamicProperty;
    use VideoCleaner;
    use VideoGetters;
    use VideoQuery;
    use VideoSetters;
    use VideoStrings;

    public $video_key;

    public $video_file;

    public $video_id;

    public $returnText;

    public $updatedText = '<fg=green>Updated ';

    public $newText = '<fg=red>Wrote ';

    public $resultCount;

    public $actionText = '';

    public $VideoInfo;

    public $fileCount;

    public $maxLen = 40;

    public $fileLen = 0;

    public $thumbExt = '.jpg';

    public $thumbDir = __INC_WEB_THUMB_DIR__;

    public $thumbType = 'preview';

    public $progressBar = false;

    public $VideoDataTable;

    public $VideoFileTable = __MYSQL_VIDEO_FILE__;

    public function __construct($key = '', $file = '')
    {
        $this->video_key  = $key;
        $this->video_file = $file;
    }

    public function clean()
    {
        $this->doClean();
        Filesystem::prunedirs($this->thumbDir . '/' . __LIBRARY__);
        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    public function clearDBValues()
    {
        $this->doClean(true);
        Filesystem::prunedirs($this->thumbDir . '/' . __LIBRARY__);
        Mediatag::$output->writeln('<comment> All Clean </comment>');
    }

    public static function videoDuration($duration, $round = 1000)
    {
        if ($round == 0) {
            $round = 1;
        }
        // utminfo();
        $seconds = (int) round($duration / $round);
        $secs    = $seconds % 60;
        $hrs     = $seconds / 60;
        $hrs     = floor($hrs);
        $mins    = $hrs % 60;
        $hrs /= 60;

        return sprintf('%02d:%02d:%02d.00', $hrs, $mins, $secs);
    }
}
