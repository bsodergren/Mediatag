<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Core\Mediatag;
use Mediatag\Utilities\MediaArray;

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
            // utmdd($name_key);
            return true;
        }

        return false;
    }

    /**
     * getArtistTransform.
     */
    public function getArtistTransform($names, $delim = ', ')
    {
        // utminfo(func_get_args());

        $namesArray  = [];
        $names       = str_replace('_1080p', '', $names);
        $names       = str_replace($this->getArtistDelim(), $delim, $names);
        $names_array = explode($delim, $names);

        $prev_name   = '';
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
            $parts = preg_split('/(?=[A-Z])/', $aName, -1, \PREG_SPLIT_NO_EMPTY);

            $aName = implode(' ', $parts);

            if (true === $this->ignoreArtist($aName)) {
                continue;
            }

            if (true === $this->getArtistFullNames()) {
                $name_key = strtolower($aName);
                if (\array_key_exists($name_key, $this->artist_match)) {
                    $aName = $this->artist_match[$name_key];
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
        if (\count($namesArray) > 0) {
            $names = implode($delim, $namesArray);

            $names = str_replace('_', ' ', $names);
            $names = str_replace('  ', ' ', $names);
            $names = ucwords($names);

            return str_replace(', ', ',', $names);
        }

        return false;
    }

    /**
     * getArtistTextTransform.
     */
    public function getArtistTextTransform($text)
    {
        // utminfo(func_get_args());

        return $text;
    }

    /**
     * getArtist.
     */
    public function getArtist()
    {
        // utminfo(func_get_args());

        $regex = $this->getArtistRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);
            if (0 != $success) {
                if (true === $this->getArtistFullNames()) {
                    if ('MFF' == $this->getGenre()) {
                        $delim = ', ';
                    } else {
                        $delim = ', ';
                        $delim = $this->getArtistDelim();
                    }
                } else {

                    $delim = ', ';
                }
                if (! \array_key_exists($this->getArtistMatch(), $output_array)) {
                    return null;
                }
                if ($output_array[$this->getArtistMatch()] == "") {
                    return null;
                }
                $names = $this->getArtistTextTransform($output_array[$this->getArtistMatch()]);


                return $this->getArtistTransform($names, $delim);
            }
        }

        return null;
    }
}
