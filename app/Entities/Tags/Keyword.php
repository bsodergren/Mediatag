<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

class Keyword extends MetaEntities
{
    public static function MetaReaderCallback()
    {
        return '/(keyw).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }
}
