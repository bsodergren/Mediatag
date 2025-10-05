<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use function array_key_exists;
use function count;
use function is_array;
use function strlen;

/**
 * Summary of MediaArray.
 */
class MediaArray
{
    /**
     * Summary of diff.
     *
     * @return array
     */
    public static function diff($array, $compare, $diff = 'key')
    {
        // utminfo(func_get_args());

        $return_array = [];
        if ($diff == 'key') {
            foreach ($array as $key => $value) {
                if (! array_key_exists($key, $compare)) {
                    $return_array[$key] = $value;
                }
            }
        } else {
            $return_array = array_diff($array, $compare);
        }

        return $return_array;
    }

    /**
     * Summary of search.
     */
    public static function search($arr, $string, $exact = false, $nodelim = false)
    {
        // utminfo(func_get_args());

        $ret = array_filter($arr, function ($value) use ($string, $exact, $nodelim) {
            if (is_array($value)) {
                if (str_contains($string, $value['name'])) {
                    if ($value['replacement'] != '') {
                        return $value['replacement'];
                    }

                    return $value['name'];
                    // utmdd([__METHOD__,__LINE__,$name]);
                }

                // return 0;
            } else {
                if ($exact === true) {
                    $value = strtolower($value);
                    $value = str_replace(' ', '_', $value);

                    if ($nodelim === true) {
                        $value  = str_replace('_', '', $value);
                        $string = str_replace('_', '', $string);
                    }

                    if ($value == $string) {
                        return 1;
                    }

                    return 0;
                }

                if (str_contains($value, $string)) {
                    return $value;
                }
            }
        });

        if (count($ret) == 0) {
            return null;
        }
        $key = array_keys($ret);

        return $ret; // [$key[0]];
    }

    public static function matchArtist($array, $string)
    {
        // utminfo(func_get_args());
        $str_array  = explode(' ', $string);
        $x          = 0;
        $namesArray = [];
        foreach ($str_array as $i => $string) {
            $string = strtolower($string);
            if (strlen($string) < 3) {
                continue;
            }

            foreach ($array as $key => $parts) {
                if (str_starts_with($parts['name'], $string)) {
                    if (! array_key_exists($i + 1, $str_array)) {
                        continue;
                    }
                    if ($parts['name'] == $string . '_' . $str_array[$i + 1]) {
                        // continue;
                    }
                    if ($parts['replacement'] != '') {
                        $namesArray[] = $parts['replacement'];
                    } else {
                        $namesArray[] = $parts['name'];
                    }

                    continue;
                }

                // $shortName = str_replace('_', '', $parts['name']);
                // if (str_starts_with( $shortName,$string)) {
                //     if ('' != $parts['replacement']) {
                //         $namesArray[] = $parts['replacement'];
                //     } else {
                //         $namesArray[] = $parts['name'];
                //     }
                // }
            }
        }
        if (count($namesArray) == 0) {
            return null;
        }

        return $namesArray;
    }

    /**
     * Summary of VideoFiles.
     */
    public static function VideoFiles(array $array, string $field, $exists = true): array
    {
        // utminfo(func_get_args());

        $videoArray = [];

        foreach ($array as $k => $file) {
            if (is_array($file)) {
                if (array_key_exists($field, $file)) {
                    $row        = $file[$field];
                    $row_exists = $file;
                    if ($field != 'video_file' && $exists) {
                        if (array_key_exists('video_file', $file)) {
                            $row_exists = $file['video_file'];
                        }
                    }
                }
            } else {
                $row        = $file;
                $row_exists = $file;
            }

            if ($exists == false) {
                $videoArray[] = $row;
            } else {
                if (file_exists($row_exists)) {
                    $videoArray[] = $row;
                }
            }
        }

        return $videoArray;
    }
}
