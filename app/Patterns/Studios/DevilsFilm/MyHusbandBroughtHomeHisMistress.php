<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios\DevilsFilm;

use Mediatag\Core\Mediatag;


use Mediatag\Utilities\MediaArray;
use Mediatag\Patterns\Studios\DevilsFilm;

const MHBHM_REGEX_COMMON = '/MHBHM[_se0-9]+?([a-zA-Z0-9]{4,})?-([_a-zA-Z]{1,})\_[0-9pkm\.]{1,}/i';

class MyHusbandBroughtHomeHisMistress extends DevilsFilm
{
    public $studio    = 'My Husband Brought Home his Mistress';

    public $artistNames;

    public $regex        = [
        'myhusbandbroughthomehismistress' => [
            'artist' => [
                'delim'               => '_',
                'pattern'             => MHBHM_REGEX_COMMON,
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            //            'studioPattern' => '/.*\_-\_((.*))(\-[0-9]{3,5}?)\.mp4/i',
            'title'  => [
                'pattern' => MHBHM_REGEX_COMMON,
                'match'   => 1,
                'delim'   => '_',
            ],
        ],
    ];

    public $names_map    = [
        'Mc Kenzie' => 'McKenzie',
        'Gi Gi'     => 'GiGi',
        'De Marco'  => 'DeMarco',
        'A J A'     => 'AJ A',
    ];

    public $artist_match = [
        'athena' => 'athena faris',
        'jamie'  => 'jamie michelle',
    ];


    public function getArtistTransform($artist_string, $delim = ', ')
    {
        utminfo();

        $checkTitle  = false;
        $names       = false;
        if (str_contains($artist_string, '-')) {
            $checkTitle    = true;
            $artist_string = str_replace('-', '_', $artist_string);
        }

        $names_array = explode($delim, str_replace($this->getArtistDelim(), $delim, $artist_string));

        $titleNames  = MediaArray::matchArtist(ARTIST_MAP, strtolower($this->video_name));
        if (\is_array($titleNames)) {
            $names_array = array_merge($names_array, $titleNames);
        }
        foreach ($names_array as $aName) {
            $aName        = str_replace('_', ' ', $aName);
            $aName        = ucwords($aName);
            $aName        = str_replace(' ', '', $aName);
            //   if (str_contains($aName, 'McKenzieLee')) {
            //       $aName = str_replace('McKenzieLee', 'MckenzieLee', $aName);
            //   }
            if (true === $this->ignoreArtist($aName)) {
                continue;
            }

            $parts        = preg_split('/(?=[A-Z])/', $aName, -1, \PREG_SPLIT_NO_EMPTY);
            $aName        = implode(' ', $parts);

            foreach ($this->names_map as $find => $replace) {
                //  // UTMlog::LogDebug("title replace",[$find,$replace,$title],"title");
                $aName = str_replace($find, $replace, $aName);
            }

            $namesArray[] = $aName;
        }
        // $c = \count($namesArray);
        // if ($c > 1) {
        //     for ($i = 1; $i < $c; ++$i) {
        //         $namesArray[0] = trim(str_replace($namesArray[$i], '', $namesArray[0]));
        //     }
        // }

        if (\count($namesArray) > 0) {
            $namesArray = array_unique($namesArray);

            $names      = implode($delim, $namesArray);

            // if (true === $checkTitle) {
            //     $names = trim(ltrim(str_replace($this->getTitle($names), '', $names), ','));
            // }
        }

        return $names;
    }

    public function getTitle($names = null)
    {
        utminfo();

        $regex = $this->getTitleRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);

            if (0 != $success) {
                if (!\array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }
                $title = str_replace($this->getTitleDelim(), ' ', $output_array[$this->gettitleMatch()]);
                if ('' == $title) {
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

                if (null !== $names) {
                    $titleParts = explode(',', $names);
                    if ($title != $titleParts[0]) {
                        $title = false;
                    }
                } else {
                    $names     = $this->getArtist();

                    // $names = str_replace(', ', ' ', $names);
                    $nameParts = explode(',', $names);
                    // $title = strtolower($title);

                    foreach ($nameParts as $n) {
                        $part  = str_replace(' ', '', $n);
                        $title = str_replace($part, $n, $title);
                    }

                    $title     = ucfirst($title);
                    $title     = trim($title);
                }

                return $title;
            }
        }

        return ' ';
    }

    public function getFilename($file)
    {
        utminfo();

        $filename = basename($file);
        if (!str_starts_with($filename, 'MHBHM')) {
            $path     = str_replace('/' . $filename, '', $file);

            // $new = preg_replace('/([a-zA-Z]+([0-9]+))?-?(.*)?_([s0-9]+)_(.*)/i', 'MHBHM_e$2_$4_$3-$5', $filename);
            preg_match('/([a-zA-Z]+([0-9]+))?-?(.*)?_([s0-9]+)_(.*)/', $filename, $output_array);

            $nArray[] = 'MHBHM';
            if ('' != $output_array[2]) {
                $nArray[] = 'e' . $output_array[2];
            }
            $nArray[] = $output_array[4];
            $nArray[] = $output_array[3];

            $nArray[] = '-' . $output_array[5];

            $name     = implode('_', $nArray);
            $name     = str_replace('__', '_', $name);

            $name     = str_replace('_-', '-', $name);
            if ($name != $filename) {
                return $path . \DIRECTORY_SEPARATOR . $name;
            }
        }

        return $file;
    }
}
