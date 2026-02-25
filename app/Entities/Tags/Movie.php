<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;
use Mediatag\Modules\Executable\Javascript;
use Mediatag\Modules\Metatags\Title;

class Movie extends MetaEntities
{
    private static $ApOption = '-H';

    private static $regexkey = 1;

    // ([a-zA-Z_-]+)(?:[\d]+)?(-|_)?(?:(e|episode)(?:[0-9]+)(?:-|_))?(?:(s|scene)?(-|_)?(?:[0-9]+))
    // private static $tvRegex = '/([a-zA-Z_-]+)([\d]+)?(-|_)?((e|episode)([0-9]+)(-|_))?((s|scene)?(-|_)?([0-9]+))/i';
    private static $tvRegex = '/^(?P<movie1>[a-zA-Z_\-]+)(?:(?:-|_)(?:(?:s|scene)|(?:e)(?:[\d]+))).*|(?:(?:[a-zA-Z0-9\-_]+)_(?:(?P<movie2>[a-zA-Z]{3,})(?:[\d]+))\.mp4)|^(?P<movie3>[a-zA-Z_\-]+)/i';

    public static $MetatagName = 'movie'; //also the DB column

    public static function MetaReaderCallback()
    {
        return '/(tvsh).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        return [self::$ApOption, self::$value];
    }

    public static function isMovie($file)
    {
        preg_match(self::$tvRegex, $file, $output_array);

        foreach ($output_array as $k => $v) {
            if (str_contains($k, 'movie') && $v != '') {
                $text = self::clean($v);

                return [self::$MetatagName => $text];
            }
        }

        return [];
    }

    public static function clean($name)
    {
        $name = trim($name, '-_');
        $name = str_replace(['_e', '-Scene'], '', $name);

        $name = str_replace(['_', '-'], ' ', $name);

        if ($name == 'MHBHM') {
            $name = 'My Husband Brought Home His Mistress';
        }

        $parts = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $aName = implode(' ', $parts);
        $aName = str_replace('  ', ' ', $aName);
        $aName = str_replace('P O V', 'POV', $aName);
        // utmdump($aName);
        $aName = (new Javascript)->read($aName);

        return $aName;
    }
}
