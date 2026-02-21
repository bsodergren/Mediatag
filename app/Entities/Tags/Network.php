<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Network extends MetaEntities
{
    private static $ApOption = '-n';

    public static function MetaReaderCallback()
    {
        return '/(tvnn).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }
}
