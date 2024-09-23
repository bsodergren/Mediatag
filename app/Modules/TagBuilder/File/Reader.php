<?php
/**
 *
 *   Plexweb
 *
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

    public $studio    = '';
    public $videoData = null;
    public $video_file;

    public $video_path;

    public $tag_array = [];

    public $title_studio;

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

        $className = '\\Pornhub\\Pornhub';

        $classPath = 'Mediatag\\Patterns\\';

        if ('Studios' == $this->video_library) {
            $studioName = strtolower($this->getSubStudio());

            if ('' != $studioName) {
                $className = $this->getStudioClass($studioName);
            }
        }
        // if ('Pornhub' == $this->video_library) {

        //     $json = new jsonReader($videoData);
        //     $studioName =  $json->getTagArray();
        //     // utmdd( $this->video_library, $studioName['studio']);


        //     if ('' != $studioName['studio']) {
        //         $className = $this->getStudioClass($studioName['studio']);
        //     }
        //     if (! class_exists($classPath . $this->video_library . '\\' . $className)) {
        //         $className =  'Pornhub';
        //     }
        // }


        if (! class_exists($classPath . $this->video_library . '\\' . $className)) {
            // UTMlog::Logger('File Studio className', $className);

            if (Option::isTrue('addClass')) {
                $options     = Option::getValue('addClass', 1);
                $classOption = null;
                if (null !== $options) {
                    $opt         = explode('=', $options);
                    $classOption = ['Studio' => $opt[1],
                        'ExtendClass'        => $this->getStudioClass($opt[1]),
                    ];
                }
                ScriptWriter::addPattern($className, ucwords($studioName), $classOption);
                $className   = $classPath . $this->video_library . '\\' . $className;
            } else {
                $this->PatternObject             = Patterns::getClassObject($className, $this);
                $this->PatternObject->video_file = $this->video_file;
            }
            //    $className = 'Mediatag\\Modules\\TagBuilder\\Patterns';
        } else {
            $className = $classPath . $this->video_library . '\\' . $className;
        }


        if (class_exists($className)) {
           self::$PatternClass              = $className;
            $this->PatternObject             = new $className($this);
            $this->PatternObject->video_file = $this->video_file;
           self::$PatternClassObj           = $this->PatternObject;
        }
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

        return $this->mapStudio($className);
    }

    public function __call($method, $arg)
    {
        utminfo();
        $getMethod = 'get' . ucfirst($method);
        // utmdd([__METHOD__,$method,$arg]);
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

    public function getSubStudio()
    {
        utminfo();
        utmdebug($this->title_studio);
        if (null === $this->title_studio) {
            $this->getStudio();
        }
        return $this->title_studio;
    }

    public function getStudio()
    {
        utminfo();

        $studio_array = [];
   
        utmdebug($this->studio);
        if (null !== $this->studio) {
            if (false == File::isPornhubfile($this->video_file)) {
                $sub_studio = '';
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
                        $sub_studio         = $matches[2];
                        $studio             = $matches[1];
                        $this->title_studio = $matches[2];
                        foreach (__SKIP_STUDIOS__ as $k) {
                            if ($studio == $k) {
                                $studio = null;
                            }
                            if ($this->title_studio == $k) {
                                $this->title_studio = '';
                            }
                        }
                    } else {
                        $studio = $matches[1];
                        if ('' != $studio) {
                            $this->title_studio = $studio;
                            foreach (__SKIP_STUDIOS__ as $k) {
                                if ($studio == $k) {
                                    $studio = null;
                                }
                                if ($this->title_studio == $k) {
                                    $this->title_studio = '';
                                }
                            }
                            $this->studio       = $studio;
                        }
                    }

                    $result       = $this->getFileTag('Studio');
                    utmdebug($result,$studio,$sub_studio);

                    // UTMlog::Logger('this->getFileTag', $result);
                    if (true == $result) {
                        if (str_contains($result, '/')) {
                            $result_array        = explode('/', $result);
                            $studio = $result_array[0];
                        } else {
                            $sub_studio = $studio;
                            $studio     = $result;

                        }
                    }
                    utmdebug($result,$studio,$sub_studio);
                    if ((null != $sub_studio) && ($studio != $sub_studio)) {
                        // if($studio == "Pov") {
                        //     $sub_studio = $studio."/".$sub_studio;
                        //     $studio = '';
                        //  } else {
                        $sub_studio = '/' . $sub_studio;
                    }

                    // }
                    $this->studio = $studio . $sub_studio;

                    utmdebug($this->studio ,$this->title_studio);
                } else {
                    $this->title_studio = 'Misc';
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
                    $this->studio = 'Pornhub';
                }
            }
        } else {
            $this->title_studio = false;
        }
utmdebug($this->studio);
        return $this->studio;
    }

    public function getGenre()
    {
        utminfo();

        $genre = '';
        if ('' == $this->genre) {

            // $res = $this->getFileTag('Genre');
            // utmdd([__METHOD__,$res]);
            $filename = $this->video_file;
            $success  = preg_match(__GENRE_REGEX__, $filename, $matches);
            if (true == $success) {
                $this->genre = $matches[1];
                //  $genre = $matches[1];
            }

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

            return $this->PatternObject->{$method}();
            //  } else {
            //      return $this->{$method}();
        }

        return null;
    }
}
