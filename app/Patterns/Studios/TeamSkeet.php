<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\TagBuilder\Patterns;

class TeamSkeet extends Patterns
{
    public $network = 'Team Skeet';
    //  public $studio = 'Team Skeet';

    public $regex = [
        'teamskeet' => [
            'artist' => [
                'pattern'             => '/[a-z-]{1,}\_([a-zA-Z_]{1,})[0-9]?\_full.*[0-9]{1,4}.*\.mp4/i',
                'delim'               => '_and_',
                'match'               => 1,
                'artistFirstNameOnly' => false,
            ],
            'studio' => [
                'pattern' => '/^([a-zA-Z0-9-]+)_.*/i', ],
        ],
        'title'     => [
            'pattern' => '/^([a-zA-Z-]+)_.*/i',
            'match'   => 1,
            // 'delim'   => '_',
        ],
    ];

    public $replace_studios = [
        'teamskeetxdantecolle'       => 'teamskeet extras',
        'busygettingbanged'          => 'Busy Getting Banged',
        'breedingmaterial'           => 'breeding material',
        'dreamingoffreeuse'          => 'Dreaming Of Freeuse',
        // 'badmilfs'                    => 'bad milfs',
        'dadcrush'                   => 'Dad Crush',
        'dyked'                      => 'dyked',
        'askyourmother'              => 'ask your mother',
        // 'theloft'                     => 'The Loft',
        'exxxtrasmall'               => 'exxxtra small',
        'familystrokes'              => 'family strokes',
        'gingerpatch'                => 'ginger patch',
        'imadeporn'                  => 'i made porn',
        'innocenthigh'               => 'innocent high',
        'lusthd'                     => 'lust hd',
        'mybabysittersclub'          => 'my babysitters club',
        'oyeloca'                    => 'oyeloca',
        'pervdriver'                 => 'perv driver',
        // 'pervmom'                     => 'perv mom',
        'pervnana'                   => 'perv nana',
        'sislovesme'                 => 'Sis Loves Me',
        'notmygrandpa'               => 'not my grandpa',
        'povlife'                    => 'pov life',
        'rubateen'                   => 'rub a teen',
        'shesnew'                    => 'shes new',
        'stayhomepov'                => 'stay home pov',
        // 'stepsiblings'                => 'step siblings',
        'teencurves'                 => 'teen curves',
        'teenpies'                   => 'teen pies',
        'teensdoporn'                => 'teens do porn',
        'thisgirlsucks'              => 'this girl sucks',
        'tittyattack'                => 'titty attack',
        'teamskeetclassics'          => 'teamskeet extras',
        'teamskeetextras'            => 'teamskeet extras',
        'teamskeetsingles'           => 'teamskeet extras',
        'mylfxonly3x'                => 'teamskeet extras',
        'teamskeetlabs'              => 'teamskeet labs',
        'teamskeetselects'           => 'teamskeet selects',
        'teamskeetxadultprime'       => 'teamskeet extras',
        'teamskeetxbaeb'             => 'teamskeet extras',
        'teamskeetxfuckingawesome'   => 'teamskeet extras',
        'teamskeetxmrluckypov'       => 'teamskeet extras',
        'teamskeetxpurgatoryx'       => 'teamskeet extras',
        'teamskeetxrawattack'        => 'raw attack',
        'teamskeetxreislin'          => 'teamskeet extras',
        'teamskeetxspizoo'           => 'teamskeet extras',
        'teamskeetxbananafever'      => 'teamskeet extras',
        'teamskeetxbrasilbimbos'     => 'teamskeet extras',
        'teamskeetxcamsoda'          => 'teamskeet extras',
        'teamskeetxerotiquetvlive'   => 'erotiquetv live',
        'teamskeetxevaelfie'         => 'teamskeet extras',
        'teamskeetxclubcastings'     => 'teamskeet extras',
        'mylfxmariskax'              => 'mariska X',
        'teamskeetxevilangel'        => 'evil angel',
        'teamskeetxherbcollins'      => 'herb collins',
        'teamskeetximpuredesire'     => 'impure desire',
        'mylfxjamesdeen'             => 'james deen',
        'teamskeetxjamesdeen'        => 'james deen',
        'teamskeetxjasonmoody'       => 'jason moody',
        'teamskeetxjoybear'          => 'joybear',
        'teamskeetxkrisskiss'        => 'kriss kiss',
        'teamskeetxmickeymod'        => 'mickey mod',
        'teamskeetxog'               => 'og',
        'teamskeetxonly3x'           => 'only3x',
        'teamskeetxozfellatioqueens' => 'oz fellatio queens',
        'teamskeetxtoughlovex'       => 'tough love',
        'teamskeetxxanderporn'       => 'xander porn',
        'teamskeetxyoungbusty'       => 'young busty',
        'teamskeetxslutinspection'   => 'X Slut Inspection',
        'teamskeetxspankmonster'     => 'teamskeet extras',
        'teamskeetxharmonyfilms'     => 'teamskeet extras',
        'teamskeetfeatures'          => 'Feature Films',
        'mylfxgrandmams'             => 'mylf x grandmams',
        'mylfxsinematica'            => 'sinematica',
        'teamskeetxsinematica'       => 'sinematica',
        'freeusemilf'                => 'freeuse milf',
        'usepov'                     => 'Use POV',
        'tomboyz'                    => 'tom boyz',
        // 'freeusefantasy'              => 'Freeuse Fantasy',
    ];

    public function getArtistTextTransform($text)
    {
        // utminfo(func_get_args());

        // if ('Teamskeet Selects' == $this->getStudio()) {
        //     return false;
        // }

        return $text;
    }
}
