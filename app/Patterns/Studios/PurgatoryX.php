<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\TagBuilder\Patterns;

class PurgatoryX extends Patterns
{
    public $regex        = [
        'purgatoryx' => [
            'artist' => [
                'name'                => 'purgatoryx',

                'pattern'             => '/([a-z]{3}[0-9]{1,5}|[a-zA-Z0-9_]{1,}_s01)_([a-zA-Z_]{1,})(_[0-9p_h]{1,})?\.mp4/i',
                'delimr'              => '_',
                'match'               => 2,
                'artistFirstNameOnly' => true,
            ],
            'title'  => [
                'pattern' => '/(([a-zA-Z0-9]+))\-[a-zA-Z0-9]+\_s[0-9]{2,3}\_(.*)\_[0-9pk]{1,6}(_h264)?.mp4/i',
                'match'   => 2,
                'delim'   => '_',
            ],
            //            'studio' => [
            //            'pattern' => '/^([a-zA-Z]{1,5})_.*/i',
            //            ],
        ],
    ];

    public $artist_match = [
        'AshleyAdams'    => 'Ashley Adams',
        'DonnieRock'     => 'Donnie Rock',
        'adamaxler'      => 'adama xler',
        'alexduca'       => 'alex duca',
        'alexmack'       => 'alex mack',
        'annabelredd'    => 'annabel redd',
        'annafoxxx'      => 'anna foxxx',
        'anthony'        => 'anthony',
        'aprilsnow'      => 'april snow',
        'ashleyadams'    => 'ashley adams',
        'ben'            => 'ben',
        'cassiecloutier' => 'cassie cloutier',
        'chadalva'       => 'chad alva',
        'charlesdera'    => 'charles dera',
        'cheriedeville'  => 'cherie deville',
        'codeysteele'    => 'codey steele',
        'codysteele'     => 'codey steele',
        'donnierock'     => 'donnie rock',
        'jayesummers'    => 'jaye summers',
        'johnstrong'     => 'john strong',
        'keiracroft'     => 'keira croft',
        'laneygrey'      => 'laney grey',
        'michaelvegas'   => 'michael vegas',
        'natashanice'    => 'natasha nice',
        'ramonnomar'     => 'ramon nomar',
        'rayblack'       => 'ray black',
        'rexryder'       => 'rex ryder',
        'robbyecho'      => 'robby echo',
        'sherlyqueen'    => 'sherly queen',
        'stirlingcooper' => 'stirling cooper',
        'vanessasierra'  => 'vanessa sierra',
        'violetmyers'    => 'violet myers',
        'willpounder'    => 'will pounder',
        'zoeymonroe'     => 'zoey monroe',
    ];
}
