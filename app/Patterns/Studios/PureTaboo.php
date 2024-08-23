<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\Patterns;

const PURETABOO_REGEX_COMMON = '/([a-zA-Z0-9\-]+)\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i';

class PureTaboo extends Patterns
{
    public $regex        = [
        'puretaboo' => [
            'artist' => [
                'pattern'             => PURETABOO_REGEX_COMMON,
                'delimr'              => '_',
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            'title'  => [
                //    'studioPattern' => '/^([a-zA-Z]{1,5})_.*/i',
                'pattern' => PURETABOO_REGEX_COMMON,
                'match'   => 1,
                'delim'   => '_',
            ],
            'studio' => [
                'pattern' => false,
            ],
            // 'studio' => [
            //     'pattern' => '/^([a-zA-Z]+)_.*.mp4/i',
            //  ],
        ],
    ];

    public $names_map    = [
        'Mc Kenzie' => 'McKenzie',
        'De Mer'    => 'DeMer',
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

        $delim       = ', ';
        $checkTitle  = false;
        $names       = false;
        $namesArray  = [];
        if (str_contains($artist_string, '-')) {
            $checkTitle    = true;
            $artist_string = str_replace('-', '_', $artist_string);
        }

        $names_array = explode($delim, str_replace($this->getArtistDelim(), $delim, $artist_string));

        foreach ($names_array as $aName) {
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

        $c           = \count($namesArray);
        if ($c > 1) {
            for ($i = 1; $i < $c; ++$i) {
                $namesArray[0] = trim(str_replace($namesArray[$i], '', $namesArray[0]));
            }
        }

        if (\count($namesArray) > 0) {
            $names = implode($delim, $namesArray);

            if (true === $checkTitle) {
                $names = trim(ltrim(str_replace($this->getTitle($names), '', $names), ','));
            }
        }

        return $names;
    }
}
