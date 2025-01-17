<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\File;

use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Utilities\ScriptWriter;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\Debug;
use UTM\Utilities\Option;

include_once __DATA_MAPS__.'/StudioMap.php';

class Reader extends TagReader
{
    use StudioReader;

    public $genre;

    public $studio;
    public $network;
    public $videoData;
    public $video_file;

    public $video_path;

    public $tag_array = [];

    public $video_key;
    public $className;

    public $video_library;

    private $PatternObject;

    public static $PatternClass;
    public static $PatternClassObj;

    public function __construct($videoData)
    {
        // utminfo(func_get_args());
        // if($this->videoData === null){
        //     $this->videoData = $videoData;
        // }
        $this->videoData = $videoData;
        $this->expandArray($videoData);

        $className = $this->video_library;
        $classPath = 'Mediatag\\Patterns\\';

        $networkName = '';

        //        $this->getnetwork();
        $this->getStudio();
        $studioName = $this->getStudioClass($this->studio);

        $studioClass = $classPath.$this->video_library.$studioName;

        // $networkName = $this->getStudioClass($this->network);
        if (Option::isTrue('addNetwork')) {
            $networkName      = Option::getValue('addNetwork', 1);
            $networkClassName = $this->getStudioClass($networkName);
            $networkClass     = $classPath.$this->video_library.$networkClassName;

            if (!class_exists($networkClass)) {
                $classOption = [
                    'Studio'      => $networkName,
                    'network'     => $networkName,
                ];
                ScriptWriter::addPattern($networkClassName, ucwords($networkName), $classOption);
            }
        }

        $classAttm[] = $studioClass;
        // utmdd($this->video_library);
        if ((!class_exists($studioClass) || Option::isTrue('addClass'))
        && ('Studios' == $this->video_library || 'HomeVideos' == $this->video_library)) {
            // UTMlog::Logger('File Studio className', $className);
            // 
            // if (Option::isTrue('addClass')) {
            $this->writeStudioClass();
            // }

            $classAttm[] = $studioClass;

            if (!class_exists($studioClass)) {
                $studioClass = 'Mediatag\\Modules\\TagBuilder\\Patterns';
                $classAttm[] = $studioClass;
            }
        }

        if (class_exists($studioClass)) {
            //  $this->PatternObject             = Patterns::getClassObject($studioClass, $this);
            // $this->PatternObject->video_file = $this->video_file;
            self::$PatternClass = $studioClass;

            $this->PatternObject = new $studioClass($this);

            // utmdd($this->PatternObject->network);
            $this->PatternObject->video_file = $this->video_file;
            self::$PatternClassObj           = $this->PatternObject;

            foreach (ARTIST_MAP as $k => $v) {
                $key = $v['name'];
                $rep = $v['replacement'];
                if ('' == $rep) {
                    $rep = $key;
                }
                $rep                  = ucwords(str_replace('_', ' ', $rep));
                $artist_matches[$key] = $rep;
            }
            foreach ($this->PatternObject->artist_match as $key => $rep) {
                if ('' == $rep) {
                    $rep = $key;
                }
                $key                  = strtolower(str_replace(' ', '_', $key));
                $rep                  = ucwords(str_replace('_', ' ', $rep));
                $artist_matches[$key] = $rep;
            }
            // utmdd($artist_matches);
            $this->PatternObject->artist_match = $artist_matches;
        }
    }

    public function getStudioClass($studio)
    {
        // utminfo(func_get_args());

        $className = ucwords($studio);
        $className = str_replace(' ', '', $className);
        $className = str_replace('&', '_', $className);

        $className = trim($className);
        if ('' == $className) {
            return '';
        }

        return '\\'.$this->mapStudio($className);
    }

    /**
     * Summary of __call.
     */
    public function __call($method, $arg)
    {
        // utminfo(func_get_args());

        $getMethod = 'get'.ucfirst($method);

        if (method_exists($this, $getMethod)) {
            $this->tag_array[$method] = $this->{$getMethod}();
        } else {
            if (null !== $this->PatternObject) {
                if (method_exists($this->PatternObject, $getMethod)) {
                    $this->tag_array[$method] = $this->PatternObject->{$getMethod}($arg);
                }
                if (method_exists($this->PatternObject, $method)) {
                    return $this->PatternObject->{$method}($arg[0]);
                }
                utmdd([__METHOD__, $this->PatternObject, $method, Debug::tracepath()]);
            }
        }

        return null;
        // utmdump([$method,$this->tag_array]);
    }

    public function mapStudio($studio)
    {
        // utminfo(func_get_args());

        $key = strtolower($studio);
        if (\array_key_exists($key, STUDIO_MAP)) {
            return STUDIO_MAP[$key];
        }

        return $studio;
    }

    public function getNetwork()
    {
        // utminfo(func_get_args());
        // utmdump(["Network",$this->network]);
        if (null === $this->network) {
            $this->network = $this->getFileTag('Network');
        }

        return $this->network;
    }

    public function getStudio()
    {
        // utminfo(func_get_args());
        if (null === $this->studio) {
            if (false == File::isPornhubfile($this->video_file)) {
                // utmdump('Not PH File', $this->video_file);
                $this->notPhFile();
            }

            if (true == File::isPornhubfile($this->video_file)) {
                // utmdump('IS PH File', $this->video_file);
                $this->isPhFile();
            }
        }

        return $this->studio;
    }

    public function getGenre()
    {
        // utminfo(func_get_args());

        $genre = '';
        if (null === $this->genre) {
            $res      = $this->getFileTag('Genre');
            $filename = \dirname($this->video_file);
            $success  = preg_match(__GENRE_REGEX__, $filename, $matches);
            if (true == $success) {
                $this->genre = $matches[1];
                //  $genre = $matches[1];
            }
            // utmdd([__METHOD__,$this->genre ]);
        }

        return $this->genre;
    }

    public function getTitle()
    {
        // utminfo(func_get_args());

        $res = $this->getFileTag('Title');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getArtist()
    {
        // utminfo(func_get_args());

        $res = $this->getFileTag('Artist');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getKeyword()
    {
        // utminfo(func_get_args());
    }

    public function getFileTag($tag)
    {
        // utminfo(func_get_args());

        $result = null;
        $method = 'get'.$tag;
        $use    = 0;
        //  // UTMlog::Logger('Class', $className);
        // UTMlog::Logger('method', $method);
        if (null !== $this->PatternObject) {
            $result = $this->PatternObject->{$method}();
            $use    = 1;
            //  } else {
            //     $result =  $this->{$method}();
        }

        return $result;
    }
}
