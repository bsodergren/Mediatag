<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Title extends MetaEntities
{
    private static $ApOption = '--title';

    public static function MetaReaderCallback()
    {
        return '/(nam).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }
}
