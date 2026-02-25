<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;

/**
 * This is an episode
 */
class Episode extends MetaEntities
{
    private static $ApOption = '-U';

    // ([a-zA-Z_-]+)(-|_)((e|episode)?([0-9]+))(-|_)((s|scene)?(-|_)?([0-9]+))
    private static $tvRegex = '/^([a-zA-Z]+(?P<epi2>[\d]+))|(?:[-_])(?:(?:e|episode)(?P<epi>[0-9]+))(?:-|_)(?:(?:s|scene)?(?:-|_?[0-9]+))|^([a-zA-Z-]+(?P<epi3>[\d]+)(-scene))|(?P<epi4>[\d]+)\.mp4/i';

    private static $regexkey = 5;

    public static $MetatagName = 'episode'; //also the DB column

    public static function MetaReaderCallback()
    {
        return '/(tvsn).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }

    public static function isEpisode($file)
    {
        preg_match(self::$tvRegex, $file, $output_array);
        utmdump([$file, $output_array]);
        foreach ($output_array as $k => $v) {
            if (str_contains($k, 'epi') && $v != '') {
                $v = ltrim($v, 0);

                return [self::$MetatagName => $v];
            }
        }

        return [];
    }
}
