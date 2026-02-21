<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

/**
 * This is a scene
 */
class Scene extends MetaEntities
{
    private static $ApOption = '-N';

    private static $tvRegex = '/(?:-|_)(?:(?:scene|s)(?:-|_)?)([0-9]+)(?:-|_)/i';

    private static $regexkey = 1;

    public static $MetatagName = 'scene'; //also the DB column

    public static function MetaReaderCallback()
    {
        return '/(tves).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }

    public static function isScene($file)
    {
        preg_match(self::$tvRegex, $file, $output_array);
        if (array_key_exists(self::$regexkey, $output_array)) {
            $number = ltrim($output_array[self::$regexkey], 0);

            return [self::$MetatagName => $number];
        }

        return [];
    }
}
