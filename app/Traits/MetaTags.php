<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\Metatags\Genre;
use Mediatag\Modules\Metatags\Keyword;
use Mediatag\Modules\Metatags\Title;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Utilities\MediaArray;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\Monolog\UTMLog;

trait MetaTags
{
    //  public $className;
    public static $tagDB;
    public static $Videokey = '';

    public function CleanMetaValue(string $tag, string $text): string
    {
        utminfo();

        $tag    = strtolower($tag);
        $method = 'clean' . ucfirst($tag);

        return trim($this->{$method}($text));
    }

    public function cleanGenre($text)
    {
        utminfo();
        return Genre::clean($text);
    }

    public function cleanArtist($text)
    {
        utminfo();

        return $text;
    }

    public function cleanKeyword($text)
    {
        utminfo();

        return Keyword::clean($text);
    }

    public function cleanTitle($text)
    {
        utminfo();

        return Title::clean($text);
    }
    public function cleanNetwork($text)
    {
        utminfo();

        return Title::clean($text);
    }

    public function cleanStudio($text): string
    {
        utminfo();

        if (\is_array($text)) {
            $array = $text;
        } else {
            $array = explode('/', $text);
        }

        $array        = array_unique($array);
        // sort($array);
        foreach ($array as $tagValue) {
            // $arr[] = trim(str_replace("  "," ",str_replace("&"," & ",$tagValue)));
            $arr[] = trim($tagValue);
        }

        if (! isset($this->videoData['video_path'])) {
            $this->videoData['video_path'] = $this->video_path;
        }

        $studio_dir   = (new FileSystem())->makePathRelative($this->videoData['video_path'], __PLEX_HOME__ . '/' . __LIBRARY__);
        $studio_array = explode('/', $studio_dir);

        // if(array_key_exists(3,$studio_array)){
        //        $key_studio   = $studio_array[1];
        // } else {
        //     $key_studio   = $studio_array[0];
        // }


        if (isset(fileReader::$PatternClass)) {

            $studio_str = fileReader::$PatternClassObj->getStudio();

            // $studio_str = trim($key_studio .'/'. $studio_str,"/");
            $arr        = explode('/', $studio_str);
            $arr        = array_unique($arr);

        }
        return implode('/', $arr);
    }

    public function sortTagList($genre, $new = null)
    {
        utminfo();

        if (\is_array($genre)) {
            $array = $genre;
        } else {
            $array = explode(',', $genre);
        }

        if (null !== $new) {
            if (str_contains($new, ',')) {
                $add_array = explode(',', $new);
                $array     = array_merge($array, $add_array);
            } else {
                $array[] = $new;
            }
        }
        $array = array_unique($array);
        sort($array);

        foreach ($array as $tagValue) {
            $arr[] = trim($tagValue);
        }

        return implode(',', $arr);
    }

    public static function mergeTag($tag, $first, $second)
    {
        utminfo();


        $firstCmp  = str_replace(" ", "", strtoupper($first));
        $secondCmp = str_replace(" ", "", strtoupper($second));

        $delim     = ',';
        if ('studio' == $tag) {
            $delim = '/';
        }
        if ('title' == $tag) {
            $delim = '';
        }
        if ('' != $secondCmp) {
            if ('' == $firstCmp) {
                $return = $second;
            } else {
                if ($firstCmp == $secondCmp) {
                    $return = $first;
                } else {

                    $return = $first . $delim . $second;
                }

            }
        } else {

            $return = $first;
        }


        if (null !== $firstCmp && $first != $second) {

            $data['video_key'] = Metatags::$Videokey;

            if ($tag == 'studio') {
                if ($firstCmp == $secondCmp) {

                    $data['studio'] = MetaTags::clean($first, $tag);
                } else {
                    $data['studio']       = MetaTags::clean($first, $tag);
                    $data['network'] = MetaTags::clean($second, $tag);
                }
            } else {
                $data[$tag] = MetaTags::clean($return, $tag);
            }


            // Mediatag::$dbconn->insert(
            //     $data,
            //     __MYSQL_VIDEO_CUSTOM__
            // );
        }


        return MetaTags::clean($return, $tag); // MetaTags::clean($return, $tag);
    }

    public function mergetags($tag_array, $tag_array2, $obj)
    {
        utminfo();

        Metatags::$Videokey = $obj;
        foreach ($tag_array as $tag => $value) {
            if (\array_key_exists($tag, $tag_array2)) {
                $value = Metatags::mergeTag($tag, $value, $tag_array2[$tag]);
            }

            $tagArray[$tag] = MetaTags::clean($value, $tag);
            $tagArray[$tag] = $value;
        }

        return $tagArray;
    }

    public static function clean($text, $tag)
    {
        utminfo();

        // UTMlog::Logger('Clean', [$tag, $text]);
        if ('artist' == $tag && null === $text) {
            return null;
        }
        $delim     = ',';
        if ('studio' == $tag) {
            $delim = '/';
        }

        if (null === MetaTags::$tagDB) {
            MetaTags::$tagDB = new TagDB();
        }

        if ('title' == $tag) {
            $arr      = explode(' ', $text);

            array_walk($arr, function (&$value) {
                $value = trim(ucwords(strtolower($value)));
            });
            $newTitle = implode(' ', $arr);
            // UTMlog::Logger('Clean tile', $tag, $newTitle);

            return $newTitle;
        }

        $method    = 'get' . ucfirst($tag);
        $newList   = [];
        $i         = 0;
        $total     = 0;
        $tag_array = explode($delim, $text);

        foreach ($tag_array as $tagValue) {
            // $tagValue = str_replace("_"," ",$tagValue);

            if (! method_exists(MetaTags::$tagDB, $method)) {
                //  $newList[] = str_replace(' ', '_', $tagValue);

                $newList[] = $tagValue;

                continue;
            }

            $value = MetaTags::$tagDB->{$method}($tagValue);
            if ('Genre' == $tag) {
                // if (__LIBRARY__ == "Home") {
                $newList[] = $tagValue;
                // }


                //  utmdd([__METHOD__,$value,$tagValue]);
            }
            if (false !== $value) {
                $newList[] = $value;
            }
        }

        $string    = implode($delim, $newList);
        $arr       = explode($delim, $string);
        array_walk($arr, function (&$value) { $value = trim(ucwords($value)); });

        $arr       = array_unique($arr, \SORT_STRING);

        $arr       = array_values($arr);
        if ('genre' == $tag) {

            if (true == MediaArray::search($arr, 'MMF')) {
                if (true == MediaArray::search($arr, 'MFF')) {
                    foreach ($arr as $v) {
                        if ('Group' == $v) {
                            continue;
                        }
                        if ('Double Penetration' == $v) {
                            continue;
                        }
                        if ('MFF' == $v) {
                            continue;
                        }

                        if ('MFF' == $v) {
                            continue;
                        }
                        $narr[] = $v;
                    }

                    $arr = $narr;
                }
            }
        }

        $max       = \count($arr);

        while ($total < 255) {
            $total = \strlen($arr[$i]) + $total + 1;
            ++$i;
            if ($i == $max) {
                break;
            }
        }

        if ($total > 255) {
            --$i;
        }
        $new_arr   = \array_slice($arr, 0, $i);

        $string    = implode($delim, $new_arr);
        if ('' == $string) {
            $string = null;
        }

        return $string;
    }

    public function expandArray($array)
    {
        utminfo();

        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
