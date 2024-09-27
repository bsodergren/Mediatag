<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Modules\TagBuilder\Patterns;

const PORNWORLD_REGEX_COMMON = '/(FS[0-9]+|GP[0-9]+)?_?(.*)_([HDP0-9]+.mp4)/i';

class PornWorld extends Patterns
{
    public $regex = [
        'pornworld' => [
            'artist' => [
                'pattern'             => PORNWORLD_REGEX_COMMON,
                'delim'               => '_',
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            'title'  => [
                'pattern' => PORNWORLD_REGEX_COMMON,
                'delim'   => '_',
                'match'   => 2,
            ],
        ],
    ];

    // public function __construct($object)
    // {
    //     utminfo();



    //     parent::__construct($object);
    //     parent::$StudioKey = $this->studio;
    // }

    public function getArtistTextTransform($text)
    {
        utminfo();

        return str_replace(['La_','De_'], ['La','De'], $text);
    }

    public function getArtistTransform($names, $delim = ', ')
    {
        utminfo();

        $namesArray  = [];
        $names       = str_replace($this->getArtistDelim(), $delim, $names);
        $names       = str_replace(['La_','De_'], ['La','De'], $names);
        $names_array = explode($delim, $names);
        $prev_name   = '';
        $skip        = false;

        foreach ($names_array as $aName) {
            $parts = preg_split('/(?=[A-Z])/', $aName, -1, \PREG_SPLIT_NO_EMPTY);

            $aName = implode(' ', $parts);


            if (true === $this->ignoreArtist($aName)) {
                continue;
            }

            if ($skip === true) {
                $skip = false;

                continue;
            }
            if (true === $this->getArtistFullNames()) {

                $name_key = strtolower($aName);
                if (\array_key_exists($name_key, $this->artist_match)) {
                    $aName = $this->artist_match[$name_key];

                    if ('' != $aName) {
                        $namesArray[] = $aName;
                        $skip         = true;

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
}
