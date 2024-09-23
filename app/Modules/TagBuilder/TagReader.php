<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\TagBuilder\DB\Reader as DbReader;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\Json\Reader as jsonReader;
use Mediatag\Modules\TagBuilder\Meta\Reader as metaReader;
use Mediatag\Traits\MetaTags;
use UTM\Utilities\Option;

class TagReader
{
    use MetaTags;

    public $tag_array = [];

    public $fileReader;

    public $metaReader;

    public $videoData;

    private object $dbConn;

    public function __construct()
    {
        utminfo();

        $this->dbConn = new Storage();
    }        // // UTMlog::Logger('data', $this->videoData);

    public function updateVideoTable($key, $tag, $value)
    {
        utminfo();

        [$_,$tag] = explode('::', $tag);
        $tag      = str_replace('set', '', strtolower($tag));
        $r        = $this->dbConn->insert(['video_key' => $key, $tag => $value], __MYSQL_VIDEO_CUSTOM__);
    }

    public function setGenre($value, $key)
    {
        utminfo();

        $meta = $this->getMetaValues();
        if (Option::isTrue('add') || Option::isTrue('drop')) {

            if (null !== $meta['genre']) {
                $meta_array = explode(',', $meta['genre']);
            }

            if (Option::isTrue('add')) {
                foreach ($value as $i => $v) {
                    if (str_contains($v, ",")) {
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
        utminfo();

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setArtist($value, $key)
    {
        utminfo();

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setStudio($value, $key)
    {
        utminfo();

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function setKeyword($value, $key)
    {
        utminfo();

        $this->updateVideoTable($key, __METHOD__, $value);

        return $value;
    }

    public function loadVideo($video)
    {
        utminfo();

        $this->videoData = $video;

        return $this;
    }

    public function getJsonValues()
    {
        utminfo();

        $json = new jsonReader($this->videoData);

        return $json->getTagArray();
    }

    public function getFileValues()
    {
        utminfo();
        $file = new fileReader($this->videoData);
        return $file->getTagArray();
    }

    public function getMetaValues()
    {
        utminfo();

        $meta = new metaReader($this->videoData);

        return $meta->getTagArray(false);
    }

    public function getDbValues()
    {
        utminfo();


        $db = new DbReader($this->videoData);
        if (null === $db->tag_array) {
            return null;
        }

        return $db->getTagArray(false);
    }

    public function getTagArray($clean = true)
    {
        utminfo();
        foreach (__META_TAGS__ as $tag) {
            $this->{$tag}();


            if (\array_key_exists($tag, $this->tag_array)) {

                if (null !== $this->tag_array[$tag]) {
                    if (true === $clean) {
                        $this->tag_array[$tag] = $this->CleanMetaValue($tag, $this->tag_array[$tag]);

                    }
                }
            }
        }

        return $this->tag_array;
    }
}
