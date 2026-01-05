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

use function array_key_exists;
use function array_slice;
use function count;
use function is_array;
use function strlen;

trait MetaTags
{
    //  public $className;
    public static $tagDB;

    public static $Videokey = '';

    public function CleanMetaValue(string $tag, string $text): string
    {
        // utminfo(func_get_args());

        $tag    = strtolower($tag);
        $method = 'clean' . ucfirst($tag);

        return trim($this->{$method}($text));
    }

    public function cleanGenre($text)
    {
        // utminfo(func_get_args());
        return Genre::clean($text);
    }

    public function cleanArtist($text)
    {
        // utminfo(func_get_args());

        return $text;
    }

    public function cleanKeyword($text)
    {
        // utminfo(func_get_args());
        return Keyword::clean($text);
    }

    public function cleanTitle($text)
    {
        // utminfo(func_get_args());
        return Title::clean($text);
    }

    public function cleanNetwork($text)
    {
        // utminfo(func_get_args());

        return $text;
    }

    public function cleanStudio($text): string
    {
        // utminfo(func_get_args());
        Mediatag::notice('Clean Studio {studio}', ['studio' => $text]);
        if (is_array($text)) {
            $array = $text;
        } else {
            $array = explode('/', $text);
        }

        $array = array_unique($array);
        // sort($array);
        foreach ($array as $tagValue) {
            // $arr[] = trim(str_replace("  "," ",str_replace("&"," & ",$tagValue)));
            $arr[] = trim($tagValue);
        }

        if (!isset($this->videoData['video_path'])) {
            $this->videoData['video_path'] = $this->video_path;
        }

        $studio_dir   = (new Filesystem)->makePathRelative($this->videoData['video_path'], __PLEX_HOME__ . '/' . __LIBRARY__);
        $studio_array = explode('/', $studio_dir);

        // if(array_key_exists(3,$studio_array)){
        //        $key_studio   = $studio_array[1];
        // } else {
        //     $key_studio   = $studio_array[0];
        // }

        if (isset(fileReader::$PatternClass)) {
            $studio_str = fileReader::$PatternClassObj->getStudio();
            // $studio_str = trim($key_studio .'/'. $studio_str,"/");
            $arr = explode('/', $studio_str);
            $arr = array_unique($arr);
        }

        return implode('/', $arr);
    }

    public function sortTagList($genre, $new = null)
    {
        // utminfo(func_get_args());

        if (is_array($genre)) {
            $array = $genre;
        } else {
            $array = explode(',', $genre);
        }

        if ($new !== null) {
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

    private static function priority($first, $second, $tag)
    {
        $firstCmp  = str_replace(' ', '', strtoupper($first));
        $secondCmp = str_replace(' ', '', strtoupper($second));

        utmdump([$tag, $firstCmp, $secondCmp]);
        $delim = ',';
        if ($tag == 'studio') {
            $delim = '/';
        }
        if ($tag == 'title') {
            // $secondCmp = '';
            $delim = '';
        }
        if ($secondCmp != '') {
            if ($firstCmp == '') {
                $return = $second;
            } else {
                if ($firstCmp == $secondCmp) {
                    $return = $first;
                } else {
                    if (str_replace($delim, '', strtoupper($firstCmp)) == $secondCmp) {
                        $return = $first;
                        // utmdump(['return first', $return]);
                    } elseif (str_replace($delim, '', strtoupper($secondCmp)) == $firstCmp) {
                        $return = $second;
                        // utmdump(['return second', $return]);
                    } else {
                        $return = $first;
                        // utmdump(['return second', $return]);

                        //                        $return = $second;
                    }
                }
            }
        } else {
            $return = $first;
            // utmdump(['return first', $return]);
        }

        return $return;
    }

    private static function priorityCombine($first, $second, $tag)
    {
        $firstCmp  = str_replace(' ', '', strtoupper($first));
        $secondCmp = str_replace(' ', '', strtoupper($second));

        // utmdump([$tag, $firstCmp, $secondCmp]);
        $delim = ',';
        if ($tag == 'studio') {
            $delim = '/';
        }
        if ($tag == 'title') {
            // $secondCmp = '';
            $delim = '';
        }
        if ($secondCmp != '') {
            if ($firstCmp == '') {
                $return = $second;
            } else {
                if ($firstCmp == $secondCmp) {
                    $return = $first;
                } else {
                    if (str_replace($delim, '', strtoupper($firstCmp)) == $secondCmp) {
                        $return = $first;
                        // utmdump(['return first', $return]);
                    } elseif (str_replace($delim, '', strtoupper($secondCmp)) == $firstCmp) {
                        $return = $second;
                        // utmdump(['return second', $return]);
                    } else {
                        $return = $first . $delim . $second;
                        // utmdump(['return second', $return]);

                        //                        $return = $second;
                    }
                }
            }
        } else {
            $return = $first;
            // utmdump(['return first', $return]);
        }

        return $return;
    }

    public static function mergeTag($tag, $first, $second, $priority = null)
    {
        // utminfo(func_get_args());

        $method = 'priority' . $priority;

        $return = self::$method($first, $second, $tag);

        // if (null !== $firstCmp && $first != $second) {
        //     $data['video_key'] = Metatags::$Videokey;

        //     if ('studio' == $tag) {
        //         if ($firstCmp == $secondCmp) {
        //             $data['studio'] = MetaTags::clean($first, $tag);
        //         } else {
        //             $data['studio']  = MetaTags::clean($first, $tag);
        //             $data['network'] = MetaTags::clean($second, $tag);
        //         }
        //     } else {
        //         $data[$tag] = MetaTags::clean($return, $tag);
        //     }

        //     // Mediatag::$dbconn->insert(
        //     //     $data,
        //     //     __MYSQL_VIDEO_CUSTOM__
        //     // );
        // }
        // utmdump(['return '.$tag, $return]);

        return MetaTags::clean($return, $tag); // MetaTags::clean($return, $tag);
    }

    public function mergetags($tag_array, $tag_array2, $obj, $priority = null)
    {
        // utminfo(func_get_args());

        Metatags::$Videokey = $obj;
        foreach ($tag_array as $tag => $value) {
            if (array_key_exists($tag, $tag_array2)) {
                $value = Metatags::mergeTag($tag, $value, $tag_array2[$tag], $priority);
            }

            $tagArray[$tag] = MetaTags::clean($value, $tag);
            $tagArray[$tag] = $value;
        }

        return $tagArray;
    }

    public static function clean($text, $tag)
    {
        // utminfo(func_get_args());

        // UTMlog::Logger('Clean', [$tag, $text]);
        if ($tag == 'artist' && $text === null) {
            return null;
        }
        $delim = ',';
        if ($tag == 'studio') {
            $delim = '/';
        }

        $tagDB = new TagDB;

        if ($tag == 'title') {
            $arr = explode(' ', $text);

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
            if (!method_exists($tagDB, $method)) {
                //  $newList[] = str_replace(' ', '_', $tagValue);

                $newList[] = $tagValue;

                continue;
            }

            $value = $tagDB->{$method}($tagValue);
            if ($tag == 'Genre') {
                $newList[] = $tagValue;
            }
            if ($value !== false) {
                $newList[] = $value;
            }
        }
        // if ($tag == 'studio') {

        //     $tmpList = $newList;
        //     array_walk($tmpList, function (&$value) {
        //         $value = str_replace(" ", "", $value);
        //         // $value = trim(ucwords($value));
        //     });
        //     $tmpList = array_unique($tmpList);
        //     $newList = array_diff($newList, $tmpList);


        // }
        $string = implode($delim, $newList);
        $arr    = explode($delim, $string);

        array_walk($arr, function (&$value) {
            $value = trim(ucwords($value));
        });

        $arr = array_unique($arr); // , \SORT_STRING);

        $arr = array_values($arr);

        if ($tag == 'genre' || $tag == 'keyword') {
            if (MediaArray::search($arr, 'Double') == true) {
                foreach ($arr as $v) {
                    if ($v == 'Double') {
                        continue;
                    }
                    $narr[] = $v;
                }
                $arr = $narr;
                unset($narr);
            }
            if (MediaArray::search($arr, 'MMF') == true) {
                if (MediaArray::search($arr, 'MFF') == true) {
                    foreach ($arr as $v) {
                        if ($v == 'Group') {
                            continue;
                        }
                        if ($v == 'Double Penetration') {
                            continue;
                        }
                        if ($v == 'MMF') {
                            // continue;
                        }

                        if ($v == 'MFF') {
                            // continue;
                        }
                        $narr[] = $v;
                    }

                    $arr = $narr;
                }
            }

            if (isset(fileReader::$PatternClass)) {
                // utmdd($this);
                $genre = fileReader::$PatternClassObj->getGenre();

                if (MediaArray::search($arr, $genre) == false) {
                    $arr[] = $genre;
                }
            }
            sort($arr);
        }
        // ;
        $max = count($arr);

        while ($total < 255) {
            $total = strlen($arr[$i]) + $total + 1;
            $i++;
            if ($i == $max) {
                break;
            }
        }

        if ($total > 255) {
            $i--;
        }
        $new_arr = array_slice($arr, 0, $i);

        $string = implode($delim, $new_arr);
        if ($string == '') {
            $string = null;
        }

        // // utmdump([__METHOD__,$method,$string]);
        return $string;
    }

    public function expandArray($array)
    {
        // utminfo(func_get_args());

        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
