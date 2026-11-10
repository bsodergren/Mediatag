<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

/**
 * This is a season
 */
class Season extends MetaEntities
{
    private static $ApOption   = '--TVSeasonNum';

    private static $tvRegex    = '/(?:-|_)(?:(?:season|s)(?:-|_)?)([0-9]+)(?:-|_)/i';

    private static $regexkey   = 1;

    public static $MetatagName = 'season'; //also the DB column

    public static function MetaReaderCallback()
    {
        $value= '/(tvsn).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
        return $value;
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }

    // public static function isSeason($file)
    // {
    //     utmdd($file,self::$tvRegex);
    //     preg_match(self::$tvRegex, $file, $output_array);
    //     if (array_key_exists(self::$regexkey, $output_array)) {
    //         $number = ltrim($output_array[self::$regexkey], 0);

    //         return [self::$MetatagName => $number];
    //     }

    //     return [];
    // }
}
