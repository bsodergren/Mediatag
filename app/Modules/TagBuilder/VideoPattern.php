<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use Mediatag\Core\Mediatag;
use ReflectionClassConstant;
use ReflectionException;

class VideoPattern
{
    const FILE_RES_LONG = '[0-9pk]{1,6}(?:_h264)?.mp4';

    public const FILE_RES_SHORT = '[0-9pk]{1,6}.mp4';
    const SEP_U  = '\_';
    const SEP_D  = '-';
    const SEP_DOT  = '\.';  

    public const ALL = '.*';
    public const SEASON = 's[0-9\-]{2,3}';
    public const EPISODE = 'e[0-9\-]{2,3}';
    public const SCENE = 'Scene[0-9\-]{1,2}';
    public const START =  '^';
    public const END =  '$';

    

/*
    DirtyLittleCheerleaderStories-Scene1_s01_ChadWhite_LilyLarimar_1080p_h264.mp4
*/
    public static function pattern(string $patterns)
    {
        $regex = '/';
        $regex .= preg_replace_callback_array(
            [
                '/\[(.*)\]/u' => function ($matches) {
                   return '('.$matches[1].')';
                },

                '/\</u' => function ($matches) {
                    return '(';
                },
                '/\>/u' => function ($matches) {
                    return ')';
                },
                '/[a-zA-Z\_]+/u' => function ($matches) {


                    try {
                        $reflection = new ReflectionClassConstant(self::class, $matches[0]);
                        return $reflection->getValue();
                    } catch (ReflectionException $e) {
                       return $matches[0];
                    }
                    
                },
                '/\s/u' => function ($matches) {
                    return '';
                }
                ],
            $patterns,
        );


        return $regex . '/i';
        
    }
}
