<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\File;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Utilities\ScriptWriter;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\Debug;
use UTM\Utilities\Option;
use Mediatag\Modules\TagBuilder\Json\Reader as jsonReader;

include_once __DATA_MAPS__ . '/StudioMap.php';

class Reader extends TagReader
{
    public $genre;

    public $studio    = null;
    public $videoData = null;
    public $video_file;

    public $video_path;

    public $tag_array = [];

    public $network;

    public $video_key;
    public $className;

    public $video_library;

    private $PatternObject;

    public static $PatternClass;
    public static $PatternClassObj;


    public function __construct($videoData)
    {
        utminfo();
        // if($this->videoData === null){
        //     $this->videoData = $videoData;
        // }
        $this->expandArray($videoData);

        $className   = '';
        $classPath   = 'Mediatag\\Patterns\\';


        $this->getnetwork();

        $studioName  = $this->getStudioClass($this->studio);
        $networkName = $this->getStudioClass($this->network);

        $studioClass = $classPath . $this->video_library . $studioName;

        $classAttm[] = $studioClass;

        if (! class_exists($studioClass)) {
            // UTMlog::Logger('File Studio className', $className);

            // if (Option::isTrue('addClass')) {
            //     $options       = Option::getValue('addClass', 1);
            //     $classOption   = null;
            //     if (null !== $options) {
            //         $opt         = explode('=', $options);
            //         $classOption = ['Studio' => $opt[1],
            //             'ExtendClass'        => $this->getStudioClass($opt[1]),
            //         ];
            //     }
            //     ScriptWriter::addPattern($studioClass, ucwords($studioName), $classOption);
            //     $studioClass   = $classPath . $this->video_library . '\\' . $className;
            // } else {
            $studioClass                     = $classPath . $this->video_library . $this->getStudioClass($this->network);

            $classAttm[]                     = $studioClass;

            if (! class_exists($studioClass)) {
                $studioClass = $classPath . $this->video_library . '\\' . $className ;
                $classAttm[] = $studioClass;
            }


            // }
            //    $className = 'Mediatag\\Modules\\TagBuilder\\Patterns';

        }
        utmdump($classAttm);
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
        utminfo();

        $className = ucwords($studio);
        $className = str_replace(' ', '', $className);
        $className = str_replace('&', '_', $className);
        //                $className = str_replace('1000', 'Thousand', $className);
        //                $className = str_replace('Private', 'PrivateVid', $className);

        $className = trim($className);
        if ($className == "") {
            return "";
        }

        return '\\' . $this->mapStudio($className);
    }

    public function __call($method, $arg)
    {
        utminfo();

        $getMethod = 'get' . ucfirst($method);

        if (method_exists($this, $getMethod)) {


            $this->tag_array[$method] = $this->{$getMethod}();
        } else {
            if (null !== $this->PatternObject) {
                if (method_exists($this->PatternObject, $method)) {
                    return $this->PatternObject->{$method}($arg[0]);
                }
            }

            utmdd([__METHOD__, Debug::tracepath()]);
        }
    }

    public function mapStudio($studio)
    {
        utminfo();

        $key = strtolower($studio);
        if (\array_key_exists($key, STUDIO_MAP)) {
            return STUDIO_MAP[$key];
        }

        return $studio;
    }

    public function getNetwork()
    {
        utminfo();

        if (null === $this->network) {
            $this->getStudio();
        }
        utmdump($this->network);
        // utmdd()
        return $this->network;
    }

    public function getStudio()
    {
        utminfo();

        $studio_array  = [];
        $network       = null;
        $studio        = null;

        if (null === $this->studio) {
            if (false == File::isPornhubfile($this->video_file)) {

                $studio_dir = (new FileSystem())->makePathRelative($this->video_path, __PLEX_HOME__ . '/' . __LIBRARY__);

                $studio_dir = str_replace('/' . $this->getGenre() . '/', '', $studio_dir);
                $arr        = explode('/', $studio_dir);
                foreach ($arr as $idx => $studio_string) {
                    foreach (__SKIP_STUDIOS__ as $k) {
                        if ($studio_string == ucwords($k)) {
                            unset($studio_array[$idx]);

                            continue 2;
                        }
                        $studio_array[$idx] = $studio_string;
                    }
                }

                $studio_dir = implode('/', $studio_array);
                if ('' != $studio_dir) {
                    $studio_dir = '/' . $studio_dir;
                    $studio_dir = str_replace('//', '/', $studio_dir);
                }

                $success    = preg_match('/\/([\w& ]+)\/?([\w\W]+)?/i', $studio_dir, $matches);

                // UTMlog::Logger('File Studio Dir', $matches);
                if (true == $success) {
                    if (\array_key_exists(2, $matches)) {

                        $network               = $matches[1];
                        $studio                = $matches[2];
                        foreach (__SKIP_STUDIOS__ as $k) {
                            if ($studio == $k) {
                                $studio = null;
                            }
                            if ($network == $k) {
                                $network = null;
                            }
                        }

                    } else {
                        $studio = $matches[1];
                        if ('' != $studio) {
                            foreach (__SKIP_STUDIOS__ as $k) {
                                if ($studio == $k) {
                                    $studio = null;
                                }
                                if ($network == $k) {
                                    $network = null;
                                }
                            }
                        }
                    }

                    //                    $this->studio        = $studio;

                    $result        = $this->getFileTag('Studio');
                    // UTMlog::Logger('this->getFileTag', $result);
                    if (true == $result) {
                        if (str_contains($result, '/')) {
                            $result_array           = explode('/', $result);
                            $network                = $studio;

                            $studio                 = $result_array[0];
                        } else {
                            $network       = $studio;
                            $studio        = $result;

                        }
                        // } else {
                        //     $network = $studio;
                        // $studio="misc";
                    }


                    // if ((null != $network) && ($studio != $network)) {


                    // if($studio == "Pov") {
                    //     $network = $studio."/".$network;
                    //     $studio = '';
                    //  } else {
                    // $this->network   = $network;
                    // } elseif ($network == $studio) {
                    //     $network = '';
                    // } else {
                    //     $network = $studio;
                    //     $studio        = '';
                    //     // utmdd([$studio,$this->studio,$network ,$this->network]);
                    // }

                    // }

                    $this->studio  = $studio;
                    if ($network === null) {
                        $network = '';
                    }
                    utmdump([$studio, $network]);
                    // } else {
                    $this->network = $network;
                }
            }

            if (true == File::isPornhubfile($this->video_file)) {
                $studio_dir   = (new FileSystem())->makePathRelative($this->video_path, __PLEX_HOME__ . '/' . __LIBRARY__);
                $studio_dir   = str_replace('/' . $this->getGenre() . '/', '', $studio_dir);
                $arr          = explode('/', $studio_dir);
                foreach ($arr as $idx => $studio_string) {
                    foreach (__SKIP_STUDIOS__ as $k) {
                        if ($studio_string == $k) {
                            unset($studio_array[$idx]);

                            continue 2;
                        }
                        $studio_array[$idx] = $studio_string;
                    }
                }
                $string       = implode('/', $studio_array);
                $studio_array = explode('/', $string);
                $this->studio = $studio_array[0];
                if ('' == $this->studio) {
                    $this->studio = '';
                }

                if ('' == $this->network) {
                    $this->network = '';


                    if (null !== $this->PatternObject) {



                        $this->network = $this->PatternObject->getNetwork();
                    }

                    if ('Studios' == $this->video_library) {
                        $this->network = 'Pornhub';
                    }

                }
            }
        } elseif ($this->network === null) {
            $this->network = "What are we doing here";
        }

        return $this->studio;
    }

    public function getGenre()
    {
        utminfo();

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
        utminfo();

        $res = $this->getFileTag('Title');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getArtist()
    {
        utminfo();

        $res = $this->getFileTag('Artist');
        if (false === $res) {
            return null;
        }

        return $res;
    }

    public function getKeyword()
    {

        utminfo();

    }

    public function getFileTag($tag)
    {
        utminfo();


        $method = 'get' . $tag;
        //  // UTMlog::Logger('Class', $className);
        // UTMlog::Logger('method', $method);
        if (null !== $this->PatternObject) {

            // utmdd(get_class($this->PatternObject));

            return $this->PatternObject->{$method}();
            //  } else {
            //      return $this->{$method}();
        }

        return null;
    }
}
