<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Metatags\Genre;
use Mediatag\Modules\Metatags\Keyword;
use Mediatag\Modules\Metatags\Title;
use Mediatag\Modules\TagBuilder\File\Reader as fileReader;
use Mediatag\Modules\TagBuilder\Json\Reader as JsonReader;
use Mediatag\Modules\TagBuilder\TagBuilder;
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

    private static $dontCombine = ['studio' => 1, 'network' => 1, 'title' => 1];

    public static $Videokey = '';

    private static function dumpTag($tag, $source, ...$array)
    {
        TagBuilder::dumpTag($tag, $source, ...$array);
    }

    public function CleanMetaValue(string $tag, string $text): string
    {
        // utminfo(func_get_args());

        $tag    = strtolower($tag);
        $method = 'clean' . ucfirst($tag);

        $out = trim($this->{$method}($text));

        return $out;
    }

    public function cleanEpisode($text)
    {
        return $text;
    }

    public function cleanMovie($text)
    {
        return $text;
    }

    public function cleanScene($text)
    {
        return $text;
    }

    public function cleanGenre($text)
    {
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
        // utmdd([__FILE__, __METHOD__, __LINE__]);

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

        $array = MediaArray::array_iunique($array);
        // sort($array);
        foreach ($array as $tagValue) {
            // $arr[] = trim(str_replace("  "," ",str_replace("&"," & ",$tagValue)));
            $arr[] = trim($tagValue);
        }

        if (! isset($this->videoData['video_path'])) {
            $this->videoData['video_path'] = $this->video_path;
        }

        $studio_dir   = (new Filesystem)->makePathRelative($this->videoData['video_path'], __PLEX_HOME__ . '/' . __LIBRARY__);
        $studio_array = explode('/', $studio_dir);

        if (array_key_exists(3, $studio_array)) {
            $key_studio = $studio_array[1];
        } else {
            $key_studio = $studio_array[0];
        }

        if (isset(fileReader::$PatternClass)) {
            $studio_str = fileReader::$PatternClassObj->getStudio();
            // utmdump(['Studio from pattern' => $studio_str]);
            $studio_str = trim($key_studio . '/' . $studio_str, '/');
            $arr        = explode('/', $studio_str);
            $arr        = MediaArray::array_iunique($arr);
        }
        // utmdump(['Clean Studio' => ['Input' => $text, 'Studio Array' => $arr,
        // 'Studio Dir'                    => $studio_dir, 'Studio Array 2' => $studio_array]]);

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
        $array = MediaArray::array_iunique($array);
        sort($array);

        foreach ($array as $tagValue) {
            $arr[] = trim($tagValue);
        }

        return implode(',', $arr);
    }

    private static function priority($first, $second, $tag)
    {
        if ($first === null) {
            $first = '';
        }
        if ($second === null) {
            $second = '';
        }

        if (count(JsonReader::$HasField) > 0) {
            // utmdump(JsonReader::$HasField);
            // utmdump('Tag ' . $tag . ' exist in json');
            if (array_key_exists($tag, JsonReader::$HasField)) {
                // utmdump('Tag ' . $tag . ' does exist');
                // utmdump(self::$dontCombine);
                // utmdump('Dont Combine Tag ' . $tag);

                if (array_key_exists($tag, self::$dontCombine)) {
                    // utmdump('Not Combining Tag ' . $tag);
                    // utmdd([$tag, $first, $second]);
                    if ($second != '') {
                        return $second;
                    }
                    if ($first != '') {
                        return $first;
                    }
                }
            }
        }
        $firstCmp  = str_replace(' ', '', strtoupper($first));
        $secondCmp = str_replace(' ', '', strtoupper($second));

        // utmdump([$tag, $firstCmp, $secondCmp]);
        $delim = ',';
        $trim  = $delim;
        if ($tag == 'studio') {
            $delim = '/';
            $trim  = $delim;

            // utmdump([$tag, $firstCmp, $secondCmp]);
        }
        if ($tag == 'title') {
            //  utmdump([$tag, $firstCmp, $secondCmp]);
            // $secondCmp = '';
            $delim = '';
            $trim  = ' ';
        }
        if ($tag == 'genre') {
            $return = trim($first . $delim . $second, $trim);
            // self::dumpTag($tag, __FUNCTION__ . ':' . __LINE__, ['first' => $first, 'second' => $second, 'return' => $return]);

            return $return;
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
                        if ($tag == 'studio') {
                            // utmdump(['return first', $return]);
                        }
                    } elseif (str_replace($delim, '', strtoupper($secondCmp)) == $firstCmp) {
                        $return = $second;
                        if ($tag == 'studio') {
                            // utmdump(['return second', $return]);
                        }
                    } else {
                        $a = str_replace($firstCmp, '', strtoupper($secondCmp));
                        $b = str_replace($secondCmp, '', strtoupper($firstCmp));
                        if ($tag == 'studio') {
                            // utmdump(['return third', $a, $b,$secondCmp]);
                        }
                        if ($b . $a == $secondCmp) {
                            $return = $second;
                        } elseif ($a . $b == $secondCmp) {
                            $return = $second;
                        } else {
                            $return = $first;
                        }
                    }
                }
            }
        } else {
            $return = $first;
            // utmdump(['return first', $return]);
        }

        // utmdump(['return', $return]);
        return trim($return, $trim);
    }

    private static function priorityCombine($first, $second, $tag)
    {
        $firstCmp  = str_replace(' ', '', strtoupper($first));
        $secondCmp = str_replace(' ', '', strtoupper($second));

        // utmdump([$tag, $firstCmp, $secondCmp]);
        $delim = ',';
        $trim  = $delim;
        if ($tag == 'studio') {
            $delim = '/';
            $trim  = $delim;
        }
        if ($tag == 'title') {
            // $secondCmp = '';
            $delim = '';
            $trim  = ' ';
        }
        if ($tag == 'genre') {
            return trim($first . $delim . $second, $trim);
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

        return trim($return, $trim);
    }

    public static function mergeTag($tag, $first, $second, $priority = null)
    {
        // utminfo(func_get_args());

        $method = 'priority' . $priority;

        if ($tag == 'title' || $tag == 'studio') {
            $method = 'priority';
            // self::dumpTag($tag, $method . ':' . __LINE__, ['return' => $return]);
        }
        $return = self::$method($first, $second, $tag);

        return $return;
    }

    public static function mergetags($tag_array, $tag_array2, $obj = null, $priority = null)
    {
        // utminfo(func_get_args());

        if (is_object($obj)) {
            self::$Videokey = $obj;
        }

        self::dumpTag('genre', __FUNCTION__, ['Tag' => $tag_array, 'Tag2' => $tag_array2, 'Priority' => $priority]);
        foreach ($tag_array as $tag => $value) {
            if (array_key_exists($tag, $tag_array2)) {
                $value2 = self::mergeTag($tag, $value, $tag_array2[$tag], $priority);
                $value  = $value2;
                unset($tag_array2[$tag]);
            }

            $tagArray[$tag] = self::clean($value, $tag);
            //$tagArray[$tag] = $value;
        }
        //
        foreach ($tag_array2 as $tag => $value) {
            if (! is_null($value)) {
                $tagArray[$tag] = self::clean($value, $tag);
            }
        }
        // utmdump(['mergeTags' => $tagArray]);

        return $tagArray;
    }

    public static function clean($text, $tag)
    {
        // utmdump(func_get_args());
        if ($tag == 'genre') {
            $text = Genre::clean($text);

            return $text;
        }

        // UTMlog::Logger('Clean', [$tag, $text]);
        if ($tag == 'artist' && $text === null) {
            return null;
        }
        if ($text === null) {
            return null;
        }
        $delim = ',';
        if ($tag == 'studio') {
            $delim = '/';
        }

        $tagDB = Storage::$DB;

        if ($tag == 'title') {
            $arr = explode(' ', $text);

            array_walk($arr, function (&$value) {
                $value = trim(ucwords(strtolower($value)));
            });
            $newTitle = implode(' ', $arr);
            // UTMlog::Logger('Clean tile', $tag, $newTitle);

            return $newTitle;
        }

        $method = 'get' . ucfirst($tag);

        $newList   = [];
        $i         = 0;
        $total     = 0;
        $tag_array = explode($delim, $text);
        foreach ($tag_array as $tagValue) {
            if (! method_exists($tagDB, $method)) {
                $newList[] = $tagValue;

                continue;
            }
            $value = $tagDB->{$method}($tagValue);

            if ($tag == 'genre') {
                $newList[] = $tagValue;
                // $newList[] = $value;
            }

            if ($value !== false) {
                $newList[] = $value;
                // utmdump(['2' => $newList]);
            }
        }

        // if ($tag == 'studio') {

        //     $tmpList = $newList;
        //     array_walk($tmpList, function (&$value) {
        //         $value = str_replace(" ", "", $value);
        //         // $value = trim(ucwords($value));
        //     });
        //     $tmpList = MediaArray::array_iunique($tmpList);
        //     $newList = array_diff($newList, $tmpList);

        // }
        // utmdump(['3' => $newList]);
        $string = implode($delim, $newList);
        // utmdump(['4' => $string]);
        $arr = explode($delim, $string);

        array_walk($arr, function (&$value) {
            $value = trim(ucwords($value));
        });
        if ($tag == 'genre') {
            // utmdump($arr);
        }
        $arr = MediaArray::array_iunique($arr); // , \SORT_STRING);
        if ($tag == 'genre') {
            // utmdump($arr);
        }
        $arr = array_values($arr);
        if ($tag == 'genre') {
            // utmdump($arr);
        }
        // $arr = array_filter($arr);
        if ($tag == 'genre') {
            // utmdump($arr);
        }

        // if ($tag == 'genre') {
        //     // utmdump($arr);
        //     $arr = self::fixGenres($arr);
        //     // self::dumpTag('genre', __FUNCTION__ . ':' . __LINE__, $arr);

        //     // self::dumpTag('genre', __FUNCTION__ . ':' . __LINE__, $arr);
        // }

        $max = count($arr);

        while ($total < 255) {
            if (! array_key_exists($i, $arr)) {
                break;
            }

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
        self::dumpTag('genre', __FUNCTION__ . ':' . __LINE__, ['new' => $new_arr, 'old' => $arr]);

        $string = implode($delim, $new_arr);
        if ($string == '') {
            $string = null;
        }
        if ($tag == 'genre') {
            // utmdd($arr);
            // self::dumpTag('genre', __FUNCTION__ . ':' . __LINE__, $string);
            // self::dumpTag('genre', __FUNCTION__ . ':' . __LINE__, $new_arr);

            // utmdump($string);
        }

        return $string;
    }

    public function expandArray($array)
    {
        // utminfo(func_get_args());
        foreach ($array as $key => $value) {
            $this->{$key} = $value;
        }
    }

    // private static function fixGenres($array)
    // {
    //     $storage = new Storage;
    //     // $array   = MediaArray::array_iunique($array);
    //     // $array   = array_filter($array);
    //     $genreArray = [];
    //     foreach ($array as $i => $value) {
    //         if ($value != '') {
    //             $genreArray[] = self::caseGenre($storage->getTag('genre', $value));
    //         }
    //     }
    //     // utmdump($genreArray);
    //     // $genreArray = array_filter($genreArray);
    //     $string     = implode(',', $genreArray);
    //     $genreArray = explode(',', $string);
    //     // utmdump($genreArray);
    //     $genreArray = MediaArray::array_iunique($genreArray);
    //     // utmdump($genreArray);

    //     return $genreArray;
    // }

    // private static function caseGenre($text)
    // {
    //     $uppercase = ['mmf', 'mff', 'pov'];
    //     $text      = trim($text);
    //     if (str_contains($text, ',')) {
    //         $pcs = explode(',', $text);
    //         foreach ($pcs as $str) {
    //             $arr[] = self::caseGenre($str);
    //         }

    //         return implode(',', $arr);
    //     }

    //     $found = MediaArray::search($uppercase, strtolower($text), exact: true);
    //     if ($found) {
    //         return strtoupper($text);
    //     }

    //     return ucwords($text);
    // }
}
