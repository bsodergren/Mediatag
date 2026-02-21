<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Genre extends MetaEntities
{
    private static $ApOption = '--genre';

    // public const META_REGEX = '/(gen).*contains\:\ (.*)/';

    public static function MetaReaderCallback()
    {
        return '/(gen).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }
}
