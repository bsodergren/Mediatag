<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\File;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\Debug;
use Mediatag\Utilities\ScriptWriter;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Modules\TagBuilder\TagReader;
use Symfony\Component\Filesystem\Filesystem;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\TagBuilder\File\StudioReader;
use Mediatag\Modules\TagBuilder\Json\Reader as jsonReader;

include_once __DATA_MAPS__ . '/StudioMap.php';

class Reader extends TagReader
{
    use StudioReader;

    public $genre;

    public $studio = null;
    public $network = null;
    public $videoData = null;
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
        utminfo(func_get_args());
        // if($this->videoData === null){
        //     $this->videoData = $videoData;
        // }
        $this->videoData = $videoData;
        $this->expandArray($videoData);

        $className = $this->video_library;
        $classPath = 'Mediatag\\Patterns\\';


        //  $this->getnetwork();
        $this->getStudio();


        $studioName = $this->getStudioClass($this->studio);
        // $networkName = $this->getStudioClass($this->network);

        $studioClass = $classPath . $this->video_library . $studioName;

        $classAttm[] = $studioClass;
        if ((!class_exists($studioClass) || Option::isTrue('addClass')) && ($this->video_library == 'Studios')) {
            // UTMlog::Logger('File Studio className', $className);


            // if (Option::isTrue('addClass')) {
            $networkName = '';

            $options = Option::getValue('addClass', 1);
            if (null === $options) {
                if (Option::isTrue('addNetwork')) {
                    $networkName = Option::getValue('addNetwork', 1);
                    $options     = "=" . $networkName;

                }
                $options = $this->studio . $options;

            }
            $classOption = [];
            if (null !== $options) {
                $opt = explode('=', $options);
                if (count($opt) > 1) {
                    $classOption = [
                        'Studio'      => $opt[1],
                        'ExtendClass' => $this->getStudioClass($opt[1]),
                        'network'     => $networkName,
                    ];
                }
                ScriptWriter::addPattern($studioName, ucwords($this->studio), $classOption);

            }
            // }

            $classAttm[] = $studioClass;

            if (!class_exists($studioClass)) {
                $studioClass = "Mediatag\\Modules\\TagBuilder\\Patterns";
                $classAttm[] = $studioClass;
            }
        }

        if (class_exists($studioClass)) {
            //  $this->PatternObject             = Patterns::getClassObject($studioClass, $this);
            // $this->PatternObject->video_file = $this->video_file;
            self::$PatternClass              = $studioClass;
            $this->PatternObject             = new $studioClass($this);
            $this->PatternObject->video_file = $this->video_file;
            self::$PatternClassObj           = $this->PatternObject;
        }

        //  utmdd($this->PatternObject );

    }

    public function getStudioClass($studio)
    {
        utminfo(func_get_args());

        $className = ucwords($studio);
        $className = str_replace(' ', '', $className);
        $className = str_replace('&', '_', $className);

        $className = trim($className);
        if ($className == "") {
            return "";
        }

        return '\\' . $this->mapStudio($className);
    }

    /**
     * Summary of __call
     * @param mixed $method
     * @param mixed $arg
     * @return mixed
     */
    public function __call($method, $arg)
    {
        utminfo(func_get_args());

        $getMethod = 'get' . ucfirst($method);

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
        utminfo(func_get_args());

        $key = strtolower($studio);
        if (\array_key_exists($key, STUDIO_MAP)) {
            return STUDIO_MAP[$key];
        }

        return $studio;
    }

    public function getNetwork()
    {
        utminfo(func_get_args());
        // utmdump(["Network",$this->network]);
        if ($this->network === null) {
            $this->network = $this->getFileTag('Network');

        }
        return $this->network;
    }

    public function getStudio()
    {
        utminfo(func_get_args());
        if (null === $this->studio) {
            if (false == File::isPornhubfile($this->video_file)) {
                // utmdump("Not PH File");
                $this->notPhFile();

            }

            if (true == File::isPornhubfile($this->video_file)) {
                // utmdump("IS PH File");
                $this->isPhFile();
            }

        }

        return $this->studio;
    }

    public function getGenre()
    {
        utminfo(func_get_args());

        $genre = '';
        if (null === $this->genre) {

            $res      = $this->getFileTag('Genre');
            $filename = $this->video_file;
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
        utminfo(func_get_args());

        $res = $this->getFileTag('Title');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getArtist()
    {
        utminfo(func_get_args());

        $res = $this->getFileTag('Artist');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getKeyword()
    {

        utminfo(func_get_args());

    }

    public function getFileTag($tag)
    {
        utminfo(func_get_args());

        $result = null;
        $method = 'get' . $tag;
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
