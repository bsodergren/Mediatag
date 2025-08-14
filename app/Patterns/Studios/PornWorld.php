<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\MediaCache;
use Mediatag\Modules\Executable\Javascript;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\MediaScraper;

use function array_key_exists;
use function array_slice;
use function count;

use const CASE_LOWER;
use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const PREG_SPLIT_NO_EMPTY;

const PORNWORLD_REGEX_COMMON = '/(FS[0-9]+|GP[0-9]+)_(.*)_([HDP0-9]+.mp4)/i';

class PornWorld extends Patterns
{
    public $studio = 'Porn World';
    public $regex  = [
        'pornworld' => [
            'artist' => [
                'pattern'             => PORNWORLD_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            // 'title'  => [
            //     'pattern' => PORNWORLD_REGEX_COMMON,
            //     'delim'   => '_',
            //     'match'   => 2,
            // ],
        ],
    ];

    // public function __construct($object)
    // {
    //     // utminfo(func_get_args());

    //     parent::__construct($object);
    //     parent::$StudioKey = $this->studio;
    // }

    public function getArtistTextTransform($text)
    {
        // utminfo(func_get_args());

        return str_replace(['La_', 'De_'], ['La', 'De'], $text);
    }

    public function getArtistTransform($names, $delim = ', ')
    {
        // utminfo(func_get_args());

        $namesArray = [];
        $names      = str_replace('_1080p', '', $names);
        //        ;
        $names = str_replace($this->getArtistDelim(), $delim, $names);
        // $names          = str_replace('_', ' ', $names);

        $names_array    = explode($delim, $names);
        $artist_matches = array_change_key_case($this->artist_match, CASE_LOWER);

        $prev_name = '';
        /*$total_names = count($names_array);
        $new_array = [];
        $key = 0;
        $new_array[$key] = '';
        for ($i = 0; $i < $total_names; ++$i) {$new_array[$key] = $new_array[$key] . $names_array[$i];
        if (1 == $i % 2) {++$key;
        $new_array[$key] = '';}}
        unset($names_array);
        $names_array = $new_array;
        */
        foreach ($names_array as $aName) {
            //  $aName = ucwords($aName);
            $parts = preg_split('/(?=[A-Z])/', $aName, -1, PREG_SPLIT_NO_EMPTY);

            $aName = implode(' ', $parts);
            // utmdump($aName);

            if (true === $this->ignoreArtist($aName)) {
                continue;
            }

            if (true === $this->getArtistFullNames()) {
                $name_key = strtolower($aName);
                $name_key = str_replace(' ', '_', $name_key);
                // utmdump([$artist_matches[0] ,$name_key]);
                if (array_key_exists($name_key, $artist_matches)) {
                    $aName = $artist_matches[$name_key];
                    if ('' != $aName) {
                        $prev_name    = $aName;
                        $namesArray[] = $aName;
                    }
                } else {
                    if (false == str_contains($prev_name, $aName)) {
                        $namesArray[] = $aName;
                    }
                }
            } else {
                $namesArray[] = $aName;
            }
        }
        // $titleNames = MediaArray::matchArtist(ARTIST_MAP, $this->getTitle());
        // if (null !== $titleNames) {
        //     $video = strtolower($this->video_name);
        //     foreach ($titleNames as $k => $name) {
        //         $tname = strtolower(str_replace('_', '', $name));

        //         if (!str_contains($video, $tname)) {
        //             unset($titleNames[$k]);
        //             continue;
        //         }
        //         $titleNames[$k] = $name = ucwords(str_replace('_', ' ', $name));
        //     }
        //     $namesArray = array_unique(array_merge($namesArray, $titleNames));
        // }

        if (count($namesArray) > 0) {
            $delim = ' ';
            $names = implode($delim, $namesArray);

            $names = str_replace('_', ' ', $names);
            $names = str_replace('  ', ' ', $names);
            $names = ucwords($names);
            // utmdump($names, $this->getTitle());

            $names = str_replace($this->getTitle(), '', $names);

            return str_replace(', ', ',', $names);
        }

        return false;
    }

    //     public function getArtistTransform($names, $delim = ', ')
    //     {
    //         // utminfo(func_get_args());
    //         $namesArray  = [];
    //         $names       = str_replace($this->getArtistDelim(), $delim, $names);
    //         $names       = str_replace(['La_', 'De_'], ['La', 'De'], $names);
    //         $names_array = explode($delim, $names);
    //         $prev_name   = '';
    //         $skip        = false;
    //         foreach ($names_array as $aName) {
    //             $parts = preg_split('/(?=[A-Z])/', $aName, -1, \PREG_SPLIT_NO_EMPTY);

    //             $aName = implode(' ', $parts);
    //             if (true === $this->ignoreArtist($aName)) {

    //                 continue;
    //             }

    //             if (true === $skip) {
    //                 $skip = false;

    //                 continue;
    //             }

    //             if (true === $this->getArtistFullNames()) {
    //                 $name_key = strtolower($aName);
    //                   utmdump( $name_key,array_key_exists($name_key, $this->artist_match));
    //                 if (\array_key_exists($name_key, $this->artist_match)) {
    //                     $aName = $this->artist_match[$name_key];

    //                     if ('' != $aName) {
    //                         $namesArray[] = $aName;
    //                         $skip         = true;
    //                     }
    //                 }
    //             } else {
    //                 $namesArray[] = $aName;
    //             }
    //         }

    //         if (\count($namesArray) > 0) {
    //             $names = implode($delim, $namesArray);

    //             $names = str_replace('_', ' ', $names);
    //             $names = str_replace('  ', ' ', $names);
    //             $names = ucwords($names);
    // utmdump($names);
    //             return str_replace(', ', ',', $names);
    //         }

    //         return false;
    //     }

    public function getTitle()
    {
        $key = $this->video_name.'_'.__FUNCTION__;

        $name       = MediaCache::get($key);
        $cache_file = __STUDIO_CACHE_DIR__.DIRECTORY_SEPARATOR.$this->video_key.'.js';
        if (false === $name) {
            // utmdd(preg_match('/(GP[0-9]+)_.*/', $this->video_name, $output_array));
            if (preg_match('/(GP[0-9]+)?(.*)_([0-9HDP]+.mp4)/', $this->video_name, $output_array)) {
                if ('' != $output_array[1]) {
                    $search = $output_array[1];
                } else {
                    $search = $output_array[2];
                    $search = str_replace('DP_ed', 'DPed', $search);

                    $search = str_replace('-_', '', $search);
                    $search = str_replace('_', ' ', $search);
                    // utmdump($search);

                    $pcs = explode(' ', $search);

                    $words      = array_slice($pcs, 0, 3);
                    $matchwords = array_slice($pcs, 0, 5);

                    $search = strtolower(implode('+', $words));

                    // $search = ($search);
                }
                // $url = 'https://pornbox.com/application/videos/search/'.$search.'/p1';
                $url = 'https://pornbox.com/store/search?q='.$search.'&skip=0&is_purchased=-1';
                https:// pornbox.com/store/search?q=ultra+horny&skip=0&sort=relevant&is_purchased=1

                                $content = MediaScraper::getUrl($url);

                //   utmdump($url);
                // utmdd(array_keys($content['content']));
                if (array_key_exists('strict_contents', $content['content'])) {
                    $name = $content['content']['strict_contents'][0]['name'];
                    $id   = $content['content']['strict_contents'][0]['id'];

                    MediaScraper::getUrl('https://pornbox.com/contents/'.$id.'/subtitles/en');

                    $videocontent = MediaScraper::getUrl('https://pornbox.com/contents/'.$id);
                    unset($videocontent['gallery']);
                    unset($videocontent['screenshots']);

                    file_put_contents($cache_file,
                        json_encode($videocontent, JSON_PRETTY_PRINT));
                } else {
                    $fileTitle = strtolower(implode(' ', $matchwords));

                    foreach ($content['content']['contents'] as $id=>$video) {
                        $search = str_replace([' -', ':'], '', $video['scene_name']);
                        // utmdump([strtolower($search),$fileTitle,str_contains(strtolower($search),$fileTitle)]);

                        if (str_contains(strtolower($search), $fileTitle)) {
                            // mkdir(__STUDIO_CACHE_DIR__, 0777, true);

                            file_put_contents($cache_file, json_encode($video, JSON_PRETTY_PRINT));

                            $name = $video['scene_name'];
                            break;
                        }
                    }
                    //                    utmdd($video);
                }
                if (false === $name) {
                    $name = $this->oldTitle();
                }
            } else {
                $name = $this->oldTitle();
            }
            if (null !== $name) {
                if (false !== $name) {
                    MediaCache::put($key, $name);
                }
            }
        }

        //  utmdump($name);
        return $name;
    }

    public function oldTitle()
    {
        $regex = $this->getTitleRegex();
        $name  = null;
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);

            if (0 != $success) {
                if (!array_key_exists($this->gettitleMatch(), $output_array)) {
                    $name = null;
                }
                $video_key = MediaFile::getVideoKey($this->video_name);

                $title = $output_array[$this->gettitleMatch()];

                $title    = str_replace('_s_', 's_', $title);
                $title    = str_replace($this->getTitleDelim(), ' ', $title);
                $pretitle = $title;
                // $title    = (new Javascript($video_key))->read($title);
                if ('' == $title) {
                    $name = null;
                }

                $name = str_replace('- ', '-', $title);
            }
        }

        return $name;
    }
}
