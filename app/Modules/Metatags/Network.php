<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Traits\Callables;
use Mediatag\Utilities\MediaArray;

class Network extends TagBuilder
{
    use Callables;

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
