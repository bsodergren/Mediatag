<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\Patterns;

class Brazzers extends Patterns
{
    public $studio          = 'Brazzers';

    public $regex           = [
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

    public $artist_match    = [
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
    //     utminfo(func_get_args());

    //     parent::__construct($object);
    //     parent::$StudioKey = $this->studio;
    // }
}
