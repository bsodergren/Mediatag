<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Keyword extends MetaEntities
{
    private static $ApOption = '--keyword';

    public static function MetaReaderCallback()
    {
        return '/(keyw).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }
}
