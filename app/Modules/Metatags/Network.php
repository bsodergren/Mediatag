<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Modules\TagBuilder\TagBuilder;

class Network extends TagBuilder
{
    public static $networkArray;

    public function __construct()
    {
        // utminfo(func_get_args());
    }

    public static function clean($text, $file = null)
    {
        // utminfo(func_get_args());

        return parent::clean($text, 'network');
    }
}
