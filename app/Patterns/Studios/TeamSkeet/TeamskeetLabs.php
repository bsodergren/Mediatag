<?php

/**
 * Command like Metatag writer for video files.
 */

namespace  Mediatag\Patterns\Studios\TeamSkeet ;

const TEAMSKEETLABS_REGEX_COMMON = '//i';


use Mediatag\Patterns\Studios\TeamSkeet\TeamSkeet;
class  TeamskeetLabs  extends  TeamSkeet
{
    public $studio = 'Teamskeet Labs';
public $network = 'Team Skeet';

    public $replace_studios = [
        'mylfxsinematica' => 'sinematica',
        // 'freeusemilf'                 => 'freeuse milf',
        // 'usepov'                      => 'Use POV',
        // 'freeusefantasy'              => 'Freeuse Fantasy',
    ];
}
