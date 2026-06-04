<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use const CASE_LOWER;
use const PREG_SPLIT_NO_EMPTY;

use Mediatag\Modules\Database\Storage;
use Mediatag\Utilities\MediaArray;

use function array_key_exists;
use function count;

trait Artist
{
    /**
     * getArtistRegex.
     */
    public function getArtistRegex()
    {
        // utminfo(func_get_args());

        return $this->getKeyValue('artist', 'pattern');
    }

    /**
     * getArtistDelim.
     */
    public function getArtistDelim()
    {
        // utminfo(func_get_args());

        return $this->getKeyValue('artist', 'delim');
    }

    /**
     * getArtistMatch.
     */
    public function getArtistMatch()
    {
        // utminfo(func_get_args());

        return $this->getKeyValue('artist', 'match');
    }

    /**
     * getArtistFullNames.
     */
    public function getArtistFullNames()
    {
        // utminfo(func_get_args());

        return $this->getKeyValue('artist', 'artistFirstNameOnly');
    }

    /**
     * ignoreArtist.
     */
    public function ignoreArtist($name)
    {
        // utminfo(func_get_args());

        $name_key = strtolower($name);
        $name_key = str_replace(' ', '_', $name_key);
        $name_key = str_replace('.', '', $name_key);
        if (MediaArray::search(IGNORE_NAME_MAP, $name_key, true, true)) {
            return true;
        }

        return false;
    }

    /**
     * getArtistTransform.
     */
    public function getArtistTransform($names, $delim = ',')
    {
        // utminfo(func_get_args());

        $namesArray     = [];
        $names          = str_replace('_1080p', '', $names);
        $names          = str_replace($this->getArtistDelim(), $delim, $names);
        $names          = str_replace('_', ' ', $names);
        $names_array    = explode($delim, $names);
        $artist_matches = array_change_key_case($this->artist_match, CASE_LOWER);
        $prev_name      = '';
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
            $aName = str_replace(' ', '', $aName);
            $parts = preg_split('/(?=[A-Z])/', $aName, -1, PREG_SPLIT_NO_EMPTY);
            $aName = implode(' ', $parts);
            //  $name_key = str_replace(' ', '', $name_key);

            if ($this->ignoreArtist($aName) === true) {
                utmdump(['ignored' => $aName]);

                continue;
            }

            if ($this->getArtistFullNames() === true) {
                $name_key    = str_replace(' ', '_', strtolower($aName));
                $matchedName = self::CheckArtist($name_key);
                // if ($matchedName === true) {
                if (count($matchedName) == 1) {
                    $matchedNames[] = $matchedName[0]['star_name'];
                    // }
                } else {
                    foreach ($parts as $index => $PartName) {
                        $matchedName = self::CheckArtist($PartName);
                        if ($matchedName !== false) {
                            if (count($matchedName) > 1) {
                                $nameMatches = self::CheckArtist($PartName . $parts[$index + 1]);
                                utmdump(['nameMatches Name' => $nameMatches]);
                                if ($nameMatches !== false) {
                                    if (count($nameMatches) > 1) {
                                        $moreNameMatches = self::CheckArtist($PartName . $parts[$index + 1] . $parts[$index + 2]);
                                        utmdump(['moreNameMatches Name' => $moreNameMatches]);
                                        if ($moreNameMatches !== false) {
                                            if (count($nameMatches) == 1) {
                                                $matchedNames[] = $PartName . ' ' . $parts[$index + 1] . ' ' . $parts[$index + 2];

                                                continue 2;
                                            }
                                        } else {
                                            $matchedNames[] = $PartName . ' ' . $parts[$index + 1];

                                            continue 2;
                                        }
                                    } else {
                                        $matchedNames[] = $PartName . ' ' . $parts[$index + 1];
                                    }

                                    continue;
                                    //utmdd([$matchedName, $matchedNames]);
                                }
                            }
                        }
                    }
                }
            } else {
                $matchedNames[] = $aName;
            }
        }

        // $titleNames = MediaArray::matchArtist(ARTIST_MAP, $this->getTitle());
        // utmdd(['titleNames' => $titleNames, 'title' => $this->getTitle()]);
        // if ($titleNames !== null) {
        //     $video = strtolower($this->video_name);
        //     foreach ($titleNames as $k => $name) {
        //         $tname = strtolower(str_replace('_', '', $name));
        //         utmdump(['VideoName' => $video, 'ArtistKey' => $tname, 'Matched' => stripos($video, $tname)]);
        //         if (! stripos($video, $tname)) {
        //             unset($titleNames[$k]);

        //             continue;
        //         }
        //         $matchedName = self::CheckArtist($tname, true);
        //         utmdump(['matched' => $matchedName, 'name' => $tname]);
        //         if ($matchedName) {
        //             // utmdd($matchedName);
        //             $titleNames[$k] = $matchedName;
        //         }
        //     }

        //     utmdd([$matchedNames, $titleNames]);

        //     // $matchedNames = MediaArray::array_iunique(array_merge($matchedNames, $titleNames));
        // }
        if (count($matchedNames) > 0) {
            $delim = ', ';
            $names = implode($delim, $matchedNames);
            $names = str_replace('_', ' ', $names);
            $names = str_replace('  ', ' ', $names);
            $names = ucwords($names);

            $nameString = str_replace(', ', ',', $names);
            utmdump(['Arist String' => [$nameString, $this->video_file]]);

            return $nameString;
        }

        return false;
    }

    /**
     * getArtistTextTransform.
     */
    public function getArtistTextTransform($text)
    {
        $text = str_replace($this->getArtistDelim(), ',', $text);

        // utmdd($this->getArtistDelim(), $delim, $names);

        // utminfo(func_get_args());

        return $text;
    }

    /**
     * getArtist.
     */
    public function getArtist()
    {
        // utminfo(func_get_args());

        $names = [];
        $regex = $this->getArtistRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);
            if ($success != 0) {
                if ($this->getArtistFullNames() === true) {
                    if ($this->getGenre() == 'MFF') {
                        $delim = ',';
                    } else {
                        $delim = ',';
                        // $delim = $this->getArtistDelim();
                    }
                } else {
                    $delim = ',';
                }
                // utmdd($this->getArtistMatch());
                $matchKeys = $this->getArtistMatch();
                if (! is_array($matchKeys)) {
                    $tmp = $matchKeys;
                    unset($matchKeys);
                    $matchKeys = [$tmp];
                }
                foreach ($matchKeys as $keyMatch) {
                    if (! array_key_exists($keyMatch, $output_array)) {
                        continue;
                    }
                    if ($output_array[$keyMatch] == '') {
                        continue;
                    }
                    $names[] = $this->getArtistTextTransform($output_array[$keyMatch]);
                }

                if (count($names) == 0) {
                    return null;
                }
                $name = implode($delim, $names);

                return $this->getArtistTransform($name, $delim);
            }
        }

        return null;
    }

    public static function CheckArtist($name, $one = false)
    {
        $getFunc = 'get';
        if ($one === true) {
            $getFunc = 'getOne';
        }
        utmdump([__FUNCTION__ => $name]);
        $name = str_replace('_', '', strtolower($name));
        $db   = Storage::$DB->mysqllib;
        // WHERE `nameKey` LIKE '%giselle%'
        if ($one === true) {
            $db->where('nameKey', $name);
        }
        $res = $db->$getFunc(__MYSQL_ARTIST_PH__, columns: ['star_name']);
        if ($one === true) {
            if (is_null($res)) {
                $res = [];
            } else {
                $t = $res;
                unset($res);
                $res = [$t];
            }
        }
        utmdump($res, $db->getLastQuery());

        if (count($res) == 0) {
            return false;
        }
        // utmdd([$res, $name]);

        return $res;
    }
}
