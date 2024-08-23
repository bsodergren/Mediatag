<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;

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
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $return_array = [];
        if ('key' == $diff) {
            foreach ($array as $key => $value) {
                if (!\array_key_exists($key, $compare)) {
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
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $ret = array_filter($arr, function ($value) use ($string, $exact, $nodelim) {
            if (\is_array($value)) {
                if (str_contains($string, $value['name'])) {
                    if ('' != $value['replacement']) {
                        return $value['replacement'];
                    }

                    return $value['name'];
                    // utmdd([__METHOD__,__LINE__,$name]);
                }

                // return 0;
            } else {
                if (true === $exact) {

                    $value = strtolower($value);
                    $value = str_replace(' ', '_', $value);

                    if (true === $nodelim) {
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

        if (0 == \count($ret)) {
            return null;
        }
        $key = array_keys($ret);

        return $ret; // [$key[0]];
    }

    public static function matchArtist($array, $string)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $namesArray = [];
        foreach ($array as $key => $parts) {
            if (str_contains($string, $parts['name'])) {
                if ('' != $parts['replacement']) {
                    $namesArray[] = $parts['replacement'];
                } else {
                    $namesArray[] = $parts['name'];
                }
                continue;
            }
            $shortName = str_replace("_", "", $parts['name']);
            if (str_contains($string, $shortName)) {
                if ('' != $parts['replacement']) {
                    $namesArray[] = $parts['replacement'];
                } else {
                    $namesArray[] = $parts['name'];
                }
            }

        }
        if (0 == \count($namesArray)) {
            return null;
        }

        return $namesArray;
    }

    /**
     * Summary of VideoFiles.
     */
    public static function VideoFiles(array $array, string $field, $exists = true): array
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $videoArray = [];

        foreach ($array as $k => $file) {
            if (\is_array($file)) {
                if (\array_key_exists($field, $file)) {
                    $row        = $file[$field];
                    $row_exists = $file;
                    if ('video_file' != $field && $exists) {
                        if (\array_key_exists('video_file', $file)) {
                            $row_exists = $file['video_file'];
                        }
                    }
                }
            } else {
                $row        = $file;
                $row_exists = $file;
            }

            if (false == $exists) {
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
