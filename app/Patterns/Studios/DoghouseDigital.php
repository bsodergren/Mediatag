<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use function array_key_exists;

const DOGHOUSEDIGITAL_REGEX_COMMON = '/([a-z\-]+)-?([0-9]{1,2})?-scene-([0-9]+)([a-z-]+)?_?([a-zA-Z_]+)?_[0-9pk]{1,5}.mp4/i';

class DoghouseDigital extends MileHighMedia
{
    public $studio = 'Doghouse Digital';
    public $regex  = [
        'doghousedigital' => [
            'artist' => [
                'pattern'             => DOGHOUSEDIGITAL_REGEX_COMMON,
                'delim'               => '_AND_',
                'match'               => 5,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => DOGHOUSEDIGITAL_REGEX_COMMON,
                'delim'   => '-',
                'match'   => 1,
            ],
        ],
    ];

    public function getTitle()
    {
        // utminfo(func_get_args());

        $regex = $this->getTitleRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);

            if (0 != $success) {
                if (!array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }

                $title = str_replace($this->getTitleDelim(), ' ', $output_array[$this->gettitleMatch()]);
                $title = trim($title);
                if ('' == $output_array[2]) {
                    $output_array[2] = '01';
                }
                $vid = 'E'.$output_array[2];
                $epi = 'Scene '.$output_array[3];

                return ucwords($title).' '.$vid.' '.$epi;
            }
        }

        return false;
    }

    private function artistTransform($artist)
    {
        // utminfo(func_get_args());

        $artist = str_replace(',', '_and_', $artist);

        return str_replace(' ', '_', $artist);
    }
}
