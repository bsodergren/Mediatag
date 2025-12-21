<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class Brazzers extends Patterns
{
    public $studio = 'Brazzers';

    public $regex = [
        'brazzers' => [
            'artist' => [
                'pattern'             => '/([a-zA-Z]{1,4})\_([a-zA-Z\_]*)\_[a-z]{2}[0-9]{1,10}/i',
                'delimr'              => '_',
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z]{1,5})_.*/i',
            ],
            'title'  => [
                'pattern' => '/([a-zA-Z0-9\-]+)\_[0-9pk]{1,6}/i',
                'match'   => 1,
                'delim'   => '-',
            ],
        ],
    ];

    public $replace_studios = [
        'bblib' => 'Big Butts like it Big',
        'bex'   => 'Brazzers Extra',
        'bgb'   => 'Baby got Boobs',
        'btas'  => 'Big Tits at School',
        'btaw'  => 'Big Tits at Work',
        'bwb'   => 'Big Wet Butts',
        'btis'  => 'Big Tits in Sports',
        'cfnm'  => 'Clothed Female Nude Males',
        'da'    => 'Doctor Adventures',
        'dm'    => 'Dirty Masseur',
        'dwp'   => 'Day with a Porn Star',
        'ham'   => 'Hot and Mean',
        'mgb'   => 'Mommy got Boobs',
        'mic'   => 'Moms in Control',
        'mlib'  => 'Milfs like it big',
        'plib'  => 'Pornstars like it big',
        'rws'   => 'Real Wife Stories',
        'sgs'   => 'Shes gonna Squirt',
        'tlib'  => 'Teens Like it Big',
        'zzs'   => 'ZZ Series',
    ];

    public $artist_match = [
        'ada'       => 'ada sanchez',
        'adriana'   => 'adriana checkic',
        'anya'      => 'anya ivy',
        'august'    => 'august taylor',
        'brandi'    => 'brandi love',
        'bridgette' => 'bridgette b',
        'diamond'   => 'diamond kitty',
        'janice'    => 'janice griffith',
        'katrina'   => 'Katrina Jade',
        'payton'    => 'Payton Preslee',
        'karlee'    => 'karlee grey',
        'katana'    => 'katana kombat',
        'keisha'    => 'keisha grey',
        'kendra'    => 'kendra lust',
        'kiara'     => 'kiara',
        'krissy'    => 'krissy lynn',
        'kristen'   => 'kristen scott',
        'leigh'     => 'leigh darby',
        'nekane'    => 'nekane',
        'nicolette' => 'nicolette shea',
        'peta'      => 'peta',
        'richelle'  => 'richelle',
        'sybil'     => 'sybil stalone',
        'valentina' => 'valentina nappi',
        'kayla'     => 'kayla kayden',
    ];

    // public function __construct($object)
    // {
    //     // utminfo(func_get_args());

    //     parent::__construct($object);
    //     parent::$StudioKey = $this->studio;
    // }

       public function getTitle()
    {
        // utminfo(func_get_args());

        $regex = $this->getTitleRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);
                            utmdump(['regex' => $regex, 'video_name' => $this->video_name, 'output_array' => $output_array]);

            if ($success != 0) {
                if (! array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }

                $title      = str_replace($this->getTitleDelim(), ' ', $output_array[$this->gettitleMatch()]);
                $titleArray = explode(' ', $title);

                utmdump($titleArray);

                foreach ($titleArray as $key => $word) {

                     if (strtolower($word) === 't') {
                        $titleArray[$key - 1] .= strtolower($word);
                        unset($titleArray[$key]);
                    }
                    if (strtolower($word) === 's') {
                        $titleArray[$key - 1] .= strtolower($word);
                        unset($titleArray[$key]);
                    }
                    if (strtolower($word) === 'x') {
                        $titleArray[$key] = strtoupper($word . '-') . $titleArray[$key + 1];
                        unset($titleArray[$key + 1]);
                    }
                    if (strtolower($word) === 'episode' || strtolower($word) === 'scene') {
                        //                        $titleArray[$key] = strtoupper($word.'-') . $titleArray[$key+1];

                        if (array_key_exists('2', $output_array)) {
                            $output_array[2] = $word . $output_array[2];
                        }

                        unset($titleArray[$key]);
                    }
                }

                if (array_key_exists('2', $output_array)) {
                    $titleArray[] = trim($output_array[2], '-');
                }

                $title = implode(' ', $titleArray);
                $title = trim($title);

                return ucwords($title);
            }
        }

        return false;
    }
}
