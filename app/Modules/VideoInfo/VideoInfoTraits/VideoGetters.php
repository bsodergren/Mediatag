<?php

namespace Mediatag\Modules\VideoInfo\VideoInfoTraits;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\VideoInfo\helpers\VideoCleaner;
use Mediatag\Modules\VideoInfo\helpers\VideoQuery;
use Mediatag\Modules\VideoInfo\helpers\VideoStrings;
use Mediatag\Modules\VideoInfo\VideoInfo;
use UTM\Utilities\DynamicProperty;

use function array_key_exists;
use function count;
use function sprintf;

trait VideoGetters
{
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

    public function getVideoInfo($key, $file)
    {
        // utminfo(func_get_args());

        $this->video_file = $file;
        $this->video_key  = $key;
        $exists           = Storage::$DB->videoExists($key, null, $this->VideoFileTable);
        if ($exists === null) {
            $data_array = Storage::$DB->createDbEntry($file, $key);
            Storage::$DB->insert($data_array);
        }

        $this->VideoInfo = $this->getVideoDetails();
        // utmdump($this->VideoInfo);

        return $this->saveVideoDetails();
    }

    public function getvideoId($key)
    {
        $this->VideoInfo = Storage::$DB->videoExists($key, null, $this->VideoFileTable);
        $this->video_id  = null;
        if ($this->VideoInfo === null) {
            return null;
        }
        $this->video_id = $this->VideoInfo['id'];

        return $this->video_id;
        // utmdd($exists);
    }

    public static function GetVideoIdByKey($key)
    {
        $o = new static(key: $key);

        return $o->getvideoId($key);
    }
}
