<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use const PREG_SPLIT_NO_EMPTY;

use Mediatag\Modules\TagBuilder\File\Reader;
use Mediatag\Traits\Patterns\Artist;
use Mediatag\Traits\Patterns\Genre;
use Mediatag\Traits\Patterns\Network;
use Mediatag\Traits\Patterns\Studio;
use Mediatag\Traits\Patterns\Title;
use Mediatag\Utilities\Strings;

use function array_key_exists;
use function count;
use function is_array;

// include_once __DATA_LISTS__.'/NamesList.php';

include_once __DATA_MAPS__ . '/StudioMap.php';

include_once __DATA_MAPS__ . '/WordMap.php';
class Patterns extends TagBuilder
{
    use Artist;
    use Genre;
    use Network;
    use Studio;
    use Title;

    public $studio;

    public $network;

    /**
     * studio_key.
     */
    public $studio_key;

    /**
     * video_name.
     */
    public $video_name;

    /**
     * className.
     */
    public $className;

    /**
     * video_file.
     */
    public $video_file;

    public $video_key;

    /**
     * genre.
     */
    public $genre;

    /**
     * regex_key.
     */
    public $regex_key;

    /**
     * artist_match.
     *
     * @var array
     */
    public $artist_match = [];

    /**
     * regex.
     */
    public $regex;

    /**
     * default_regex.
     *
     * @var array
     */
    public $default_regex = [
        'default' => [
            'artist' => [
                'name'                => 'default',
                'pattern'             => '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i',
                'delim'               => '_',
                'match'               => 3,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => '/(([a-zA-Z0-9\-]+))\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i',
                'match'   => 2,
                'delim'   => '_',
            ],
            'studio' => [
                'pattern' => false,
            ],
        ],
    ];

    public static $StudioKey = false;

    /**
     * replace_studios.
     *
     * @var array
     */
    public $replace_studios = [];

    /**
     * __construct.
     */
    public function __construct($object = null)
    {
        // utminfo($object);
        if ($object !== null) {
            self::boot($this);
            $this->className  = $object->className;
            $this->video_name = $object->video_name;
            $this->video_key  = $object->video_key;

            $studio           = strtolower($object->getStudio());
            $this->studio_key = str_replace(' ', '', $studio);
            $this->studio_key = $this->mapStudio($this->studio_key);
            // /   $this->network =  $object->getNetwork();
            if ($this->network === null) {
                $this->network = $this->metaNetwork();
            }

            if ($this->studio_key == '') {
                $this->studio_key = 'default';
            }

            if ($this->regex === null) {
                $this->regex = [];
            }
            $this->regex = array_merge($this->default_regex, $this->regex);
            if ($this->network !== null) {
                if ($this->studio !== null) {
                    self::$StudioKey = $this->studio;
                }
            }
            // utmdd($this->network);
        }
    }

    public static function boot($obj)
    {
        $mainClass = $obj::class;
        // utminfo($mainClass);
        $classes = class_parents($obj);
        $class   = reset($classes);
        if (str_contains($class, 'Patterns')) {
            $class = $mainClass;
        }

        [$classPath, $className] = self::classStudio($class);

        if ($classPath != 'Studios') {
            if (Reader::$PatternClass !== null) {
                // utmdd([ $classPath, $className ]);

                [$classPath, $className] = self::classStudio(Reader::$PatternClass);
                $obj->studio             = $className;
            }

            // $obj->network        = $className;
            return 0;
        }

        return $obj;
    }

    private static function classStudio($class)
    {
        $classparts = explode('\\', $class);
        $classPath  = $classparts[count($classparts) - 2];

        $className = end($classparts);
        $className = Strings::StudioName($className, false);
        $parts     = preg_split('/(?=[A-Z])/', $className, -1, PREG_SPLIT_NO_EMPTY);
        $className = implode(' ', $parts);

        return [$classPath, $className];
    }

    public static function getClassObject($className, $obj)
    {
        return new class($obj) extends Patterns {};
    }

    /**
     * getKeyValue.
     */
    private function getKeyValue($tag, $key)
    {
        // utminfo(func_get_args());
        $regex  = $this->regex;
        $studio = strtolower($this->studio_key);

        // $this->getKeyName($studio);

        if (! array_key_exists($studio, $regex)) {
            $studio = 'default';
        }

        if (! is_null($this->network)) {
            $network = str_replace(' ', '', $this->network);
            $network = strtolower($network);

            if (array_key_exists($network, $regex)) {
                $studio = $network;
            }
        }

        $array = $regex[$studio];

        if (! array_key_exists($tag, $array)) {
            $array = $regex['default'];

            return false;
        }
        $array = $array[$tag];
        if (! array_key_exists($key, $array)) {
            $array = $regex['default'];
            $array = $array[$tag];
        }

        return $array[$key];
    }

    /**
     * getKeyName.
     */
    public function getKeyName($key)
    {
        // utmdd($key);
    }

    /**
     * getKeyword.
     */
    public function getKeyword() {}

    /**
     * getFilename.
     */
    public function getFilename($file)
    {
        // utminfo(func_get_args());

        return $file;
    }

    public function getStudio()
    {
        // utminfo(func_get_args());
        $studio = $this->metaStudio();

        if ($studio !== null && $studio != '') {
            $this->studio = $studio;
        }

        // if (null !== $this->network) {
        //     if ($this->network == $this->studio) {
        //         $this->studio = "Misc";
        //     }
        //     return  $this->network . '/' . $this->studio;
        // }

        return $this->studio;
    }

    public function getNetwork()
    {
        $network       = $this->metaNetwork();
        $this->network = $network;

        return $network;
    }

    public static function customStudio($key_studio, $arr)
    {
        // utminfo(func_get_args());

        if (is_array($arr)) {
            $studioArray[] = $key_studio;
            foreach ($arr as $studio) {
                if ($key_studio == $studio) {
                    continue;
                }
                $studioArray[] = $studio;
            }
        }

        return $studioArray;
    }
}
