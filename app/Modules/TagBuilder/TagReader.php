<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use Mediatag\Core\Mediatag;
use Mediatag\Entities\MetaEntities;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\TagBuilder\DB\Reader as DbReader;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\Json\Reader as jsonReader;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Mediatag\Traits\DynamicProperty;
use Mediatag\Traits\MetaTags;
use UTM\Utilities\Option;

use function array_key_exists;

class TagReader
{
    use DynamicProperty;
    use MetaTags;

    public $tag_array = [];

    public $fileReader;

    public $metaReader;

    public $videoData;

    private object $dbConn;

    public function __construct()
    {
        // utminfo(func_get_args());

        $this->dbConn = new Storage;
    }        // // UTMlog::Logger('data', $this->videoData);

    public function updateVideoTable($key, $tag, $value)
    {
        // utminfo(func_get_args());

        [$_,$tag] = explode('::', $tag);
        $tag      = str_replace('set', '', strtolower($tag));
        $r        = $this->dbConn->insert(['video_key' => $key, $tag => $value], __MYSQL_VIDEO_METADATA__);
    }

    public function setGenre($value, $key)
    {
        // utminfo(func_get_args());

        $meta = $this->getMetaValues();
        if (Option::isTrue('add') || Option::isTrue('drop')) {
            if ($meta['genre'] !== null) {
                $meta_array = explode(',', $meta['genre']);
            }

            if (Option::isTrue('add')) {
                foreach ($value as $i => $v) {
                    if (str_contains($v, ',')) {
                        $parts = explode(',', $v);
                        foreach ($parts as $ii => $vv) {
                            $meta_array[] = $vv;
                        }

                        continue;
                    }
                    $meta_array[] = $v;
                }

                $updatedArray = $meta_array;
            }

            if (Option::isTrue('drop')) {
                foreach ($meta_array as $genre) {
                    if ($genre != $value[0]) {
                        $updatedArray[] = $genre;
                    }
                }
            }

            $updatedArray = array_unique($updatedArray);
            $value        = implode(',', $updatedArray);
        }

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setTitle($value, $key)
    {
        // utminfo(func_get_args());

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setArtist($value, $key)
    {
        // utminfo(func_get_args());

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setStudio($value, $key)
    {
        // utminfo(func_get_args());

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setKeyword($value, $key)
    {
        // utminfo(func_get_args());

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function loadVideo($video)
    {
        // utminfo(func_get_args());

        $this->videoData = $video;

        return $this;
    }

    public function getJsonValues()
    {
        // utminfo(func_get_args());

        $json = new jsonReader($this->videoData);

        return $json->getTagArray();
    }

    public function getFileValues()
    {
        // utminfo(func_get_args());
        $file = new fileReader($this->videoData);

        return $file->getTagArray();
    }

    public function getMetaValues()
    {
        // utminfo(func_get_args());

        $meta = new metaReader($this->videoData);

        return $meta->getTagArray(false);
    }

    public function getDbValues()
    {
        // utminfo(func_get_args());

        $db = new DbReader($this->videoData);
        if ($db->tag_array === null) {
            return null;
        }

        return $db->getTagArray(false);
    }

    public function getTagArray($clean = true)
    {
        // utminfo(func_get_args());
        foreach (__META_TAGS__ as $tag) {
            $this->{$tag}();
            // utmdump([$tag, $this->tag_array]);

            if (array_key_exists($tag, $this->tag_array)) {
                Mediatag::notice("Metatags {tag} => '{value}'", ['tag' => $tag, 'value' => $this->tag_array[$tag]]);

                if ($this->tag_array[$tag] !== null) {
                    if ($clean === true) {
                        // utmdd([__FILE__, __METHOD__, __LINE__]);
                        $this->tag_array[$tag] = $this->CleanMetaValue($tag, $this->tag_array[$tag]);
                    }
                }
            }
        }

        if (is_array($this->videoData)) {
            if (array_key_exists('video_name', $this->videoData)) {
                $tvParams = MetaEntities::CheckForTvParams($this->videoData['video_name']);
                if (is_array($tvParams)) {
                    foreach ($tvParams as $tag => $valure) {
                        $this->tag_array[$tag] = $valure;
                    }
                }
            }
        }

        Mediatag::notice("Metatag '{value}'", ['value' => $this->tag_array]);

        return $this->tag_array;
    }
}
