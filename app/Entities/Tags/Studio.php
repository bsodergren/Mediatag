<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Studio extends MetaEntities
{
    private static $ApOption = '--album';

    public static function MetaReaderCallback()
    {
        return '/(alb).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }
}
