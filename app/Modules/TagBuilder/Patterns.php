<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\File\Reader;
use Mediatag\Utilities\Strings;
use Mediatag\Traits\Patterns\Genre;
use Mediatag\Traits\Patterns\Title;
use Mediatag\Traits\Patterns\Artist;
use Mediatag\Traits\Patterns\Studio;

// include_once __DATA_LISTS__.'/NamesList.php';

include_once __DATA_MAPS__ . '/StudioMap.php';

include_once __DATA_MAPS__ . '/WordMap.php';
class Patterns extends TagBuilder
{
    use Artist;
    use Genre;
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
    public $artist_match     = [];

    /**
     * regex.
     */
    public $regex;

    /**
     * default_regex.
     *
     * @var array
     */
    public $default_regex    = [
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
    public $replace_studios  = [];

    /**
     * __construct.
     */
    public function __construct($object)
    {
        utminfo();
        self::boot($this);
        $this->className  = $object->className;
        $this->video_name = $object->video_name;
        $studio           = strtolower($object->getnetwork());
        $this->studio_key = str_replace(' ', '', $studio);
        $this->studio_key = $this->mapStudio($this->studio_key);
        if ('' == $this->studio_key) {
            $this->studio_key = 'default';
        } if (null === $this->regex) {
            $this->regex = [];
        }
        $this->regex      = array_merge($this->default_regex, $this->regex);
        if (null !== $this->network) {
            if (null !== $this->studio) {
                self::$StudioKey = $this->studio;
            }
        }
    }

    public static function boot($obj)
    {
        $classes                  = class_parents($obj);
        $class                    = reset($classes);

        [$classPath, $className ] = self::classStudio($class);


        if ($classPath != "Studios"
        ) {

            if (Reader::$PatternClass !== null) {
                [$classPath, $className ] = self::classStudio(Reader::$PatternClass);
                $obj->studio              = $className;
            }
            return 0;

        }

        $obj->network        = $className;

    }
    private static function classStudio($class)
    {
        $classparts            = explode("\\", $class);
        $classPath             = $classparts[count($classparts)-2];

        $className             = end($classparts);
        $className             = Strings::StudioName($className, false);
        $parts                 = preg_split('/(?=[A-Z])/', $className, -1, \PREG_SPLIT_NO_EMPTY);
        $className             = implode(' ', $parts);
        return [$classPath,$className];
    }
    public static function getClassObject($className, $obj)
    {

        return new class ($obj) extends Patterns {};
    }
    /**
     * getKeyValue.
     */
    private function getKeyValue($tag, $key)
    {
        utminfo();
        $regex         = $this->regex;
        $network = str_replace(' ', '', $this->studio);
        $network = $this->mapStudio($network);
        $studio        = strtolower($this->studio_key);
        $network = strtolower($network);

        
        $this->getKeyName($studio);

        if (\array_key_exists($studio, $regex)) {
            // $studio = $studio;
        } elseif (\array_key_exists($network, $regex)) {
            $studio = $network;
        } else {
            $studio = 'default';
        }

        $array         = $regex[$studio];
        if (!\array_key_exists($tag, $array)) {
            $array = $regex['default'];

            return null;
        }
        $array         = $array[$tag];
        if (!\array_key_exists($key, $array)) {
            $array = $regex['default'];
            $array = $array[$tag];
        }

        return $array[$key];
    }

    /**
     * getKeyName.
     */
    public function getKeyName($key) {}

    /**
     * getKeyword.
     */
    public function getKeyword() {}

    /**
     * getFilename.
     */
    public function getFilename($file)
    {
        utminfo();

        return $file;
    }

    public function getStudio()
    {
        utminfo();
        $studio = $this->metaStudio();

        if ($studio !== null && $studio != "") {
            $this->studio =  $studio;
        }


        // if (null !== $this->network) {
        //     if ($this->network == $this->studio) {
        //         $this->studio = "Misc";
        //     }
        //     return  $this->network . '/' . $this->studio;
        // }

        return $this->studio;
    }

public function getNetwork(){
    utmdd(__METHOD__);
    return 'network';
}
    public static function customStudio($key_studio, $arr)
    {
        utminfo();

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
