<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Symfony\Component\Console\Input\InputArgument;

class Options extends MediaOptions
{
    use Lang;

    public function Arguments($varName = null, $description = null)
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
