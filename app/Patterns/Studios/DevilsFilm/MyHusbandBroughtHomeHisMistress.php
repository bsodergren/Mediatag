<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\DevilsFilm;

use const DIRECTORY_SEPARATOR;

const MHBHM_REGEX_COMMON = '/MHBHM[_se0-9]+?([a-zA-Z0-9]{4,})?-([_a-zA-Z]{1,})\_[0-9pkm\.]{1,}/i';
const MHBHM_REGEX_TITLE  = '/(?P<title>MyHusbandBroughtHomeHisMistress)(?P<movie>[0-9]{0,2})(-(?P<cast>[a-zA-Z]+)(?P<scene>[_s0-9]{0,5})?|(?P<scene2>[_s0-9]{0,5})?(?P<cast2>[_a-zA-Z]+))_.*/i';

use Mediatag\Patterns\Studios\DevilsFilm\DevilsFilm;

class MyHusbandBroughtHomeHisMistress extends DevilsFilm
{
    public $studio = 'My Husband Brought Home His Mistress';

    public $network = 'Devils Film';

    // public $artistNames;
    public $regex = [
        'myhusbandbroughthomehismistress' => [
            'artist' => [
                'delim'               => '_',
                'pattern'             => MHBHM_REGEX_TITLE,
                'match'               => ['cast', 'cast2'],
                'artistFirstNameOnly' => true,
            ],
            //            'studioPattern' => '/.*\_-\_((.*))(\-[0-9]{3,5}?)\.mp4/i',
            'title'  => [
                'pattern' => MHBHM_REGEX_TITLE,
                'match'   => 'title',
                'delim'   => '_',
            ],
        ],
    ];

    // public $names_map    = [
    //     'Mc Kenzie' => 'McKenzie',
    //     'Gi Gi'     => 'GiGi',
    //     'De Marco'  => 'DeMarco',
    //     'A J A'     => 'AJ A',
    // ];

    // public $artist_match = [
    //     'athena' => 'athena faris',
    //     'jamie'  => 'jamie michelle',
    // ];

    public function getTitle($names = null)
    {
        // utminfo(func_get_args());

        $regex = $this->getTitleRegex();
        if ($regex) {
            // utmdump($regex);
            $success = preg_match($regex, $this->video_name, $output_array);
            // utmdd($output_array);
            if ($success != 0) {
                if (! \array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }
                $title = str_replace($this->getTitleDelim(), ' ', $output_array[$this->gettitleMatch()]);

                if ($title == '') {
                    return null;
                }

                // foreach (WORD_MAP as $find => $replace) {
                //     //  // UTMlog::LogDebug("title replace",[$find,$replace,$title],"title");
                //     //     $title = str_replace($find, $replace, $title);
                // }

                $parts = preg_split('/(?=[A-Z])/', $title, -1, \PREG_SPLIT_NO_EMPTY);
                $title = implode(' ', $parts);

                $title = preg_replace('/([0-9]+)/', ' $1', $title);
                $title = str_replace('  ', ' ', $title);
                $title = str_replace(' -', '-', $title);
                $title = str_replace('- ', '-', $title);

                if ($names !== null) {
                    $titleParts = explode(',', $names);
                    // utmdd([$title,$names]);

                    if ($title != $titleParts[0]) {
                        $title = false;
                    }
                } else {
                    // $names     = $this->getArtist();

                    // // $names = str_replace(', ', ' ', $names);
                    // $nameParts = explode(',', $names);
                    // // $title = strtolower($title);

                    // foreach ($nameParts as $n) {
                    //     $part  = str_replace(' ', '', $n);
                    //     $title = str_replace($part, $n, $title);
                    // }

                    // $title     = ucfirst($title);
                    // $title     = trim($title);
                }

                return $title;
            }
        }

        return ' ';
    }

    // public function getArtistTransform($names, $delim = ', ')
    // {
    //     // utminfo(func_get_args());

    //     $namesArray = [];
    //     $names      = str_replace('_1080p', '', $names);
    //     //        ;
    //     $names = str_replace($this->getArtistDelim(), $delim, $names);
    //     // $names          = str_replace('_', ' ', $names);

    //     $names_array    = explode($delim, $names);
    //     $artist_matches = array_change_key_case($this->artist_match, CASE_LOWER);

    //     $prev_name = '';
    //     /*$total_names = count($names_array);
    //     $new_array = [];
    //     $key = 0;
    //     $new_array[$key] = '';
    //     for ($i = 0; $i < $total_names; ++$i) {$new_array[$key] = $new_array[$key] . $names_array[$i];
    //     if (1 == $i % 2) {++$key;
    //     $new_array[$key] = '';}}
    //     unset($names_array);
    //     $names_array = $new_array;
    //     */
    //     foreach ($names_array as $aName) {
    //         //  $aName = ucwords($aName);

    //         $parts = preg_split('/(?=[A-Z])/', $aName, -1, PREG_SPLIT_NO_EMPTY);

    //         $aName = implode(' ', $parts);
    //         // // utmdump($aName);

    //         if ($this->ignoreArtist($aName) === true) {
    //             continue;
    //         }

    //         if ($this->getArtistFullNames() === true) {
    //             $name_key = strtolower($aName);
    //             $name_key = str_replace(' ', '_', $name_key);
    //             utmdd([$artist_matches['kylie_rocket'], $name_key]);
    //             if (array_key_exists($name_key, $artist_matches)) {
    //                 $aName = $artist_matches[$name_key];
    //                 if ($aName != '') {
    //                     $prev_name    = $aName;
    //                     $namesArray[] = $aName;
    //                 }
    //             } else {
    //                 if (str_contains($prev_name, $aName) == false) {
    //                     $namesArray[] = $aName;
    //                 }
    //             }
    //         } else {
    //             $namesArray[] = $aName;
    //         }
    //     }
    //     // $titleNames = MediaArray::matchArtist(ARTIST_MAP, $this->getTitle());
    //     // if (null !== $titleNames) {
    //     //     $video = strtolower($this->video_name);
    //     //     foreach ($titleNames as $k => $name) {
    //     //         $tname = strtolower(str_replace('_', '', $name));

    //     //         if (!str_contains($video, $tname)) {
    //     //             unset($titleNames[$k]);
    //     //             continue;
    //     //         }
    //     //         $titleNames[$k] = $name = ucwords(str_replace('_', ' ', $name));
    //     //     }
    //     //     $namesArray = MediaArray::array_iunique(array_merge($namesArray, $titleNames));
    //     // }

    //     if (count($namesArray) > 0) {
    //         $delim = ' ';
    //         $names = implode($delim, $namesArray);

    //         $names = str_replace('_', ' ', $names);
    //         $names = str_replace('  ', ' ', $names);
    //         $names = ucwords($names);
    //         // // utmdump($names, $this->getTitle());

    //         $names = str_replace($this->getTitle(), '', $names);

    //         return str_replace(', ', ',', $names);
    //     }

    //     return false;
    // }

    public function getFilename($file)
    {
        // utminfo(func_get_args());

        $filename = basename($file);
        if (! str_starts_with($filename, 'MHBHM')) {
            $path = str_replace('/' . $filename, '', $file);

            // $new = preg_replace('/([a-zA-Z]+([0-9]+))?-?(.*)?_([s0-9]+)_(.*)/i', 'MHBHM_e$2_$4_$3-$5', $filename);
            preg_match('/([a-zA-Z]+([0-9]+))?-?(.*)?_([s0-9]+)_(.*)/', $filename, $output_array);

            $nArray[] = 'MHBHM';
            if ($output_array[2] != '') {
                $nArray[] = 'e' . $output_array[2];
            }
            $nArray[] = $output_array[4];
            $nArray[] = $output_array[3];

            $nArray[] = '-' . $output_array[5];

            $name = implode('_', $nArray);
            $name = str_replace('__', '_', $name);

            $name = str_replace('_-', '-', $name);
            if ($name != $filename) {
                return $path . DIRECTORY_SEPARATOR . $name;
            }
        }

        return $file;
    }
}
