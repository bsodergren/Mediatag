<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use Mediatag\Core\MediaOptions;
use Symfony\Component\Console\Input\InputArgument;

class Options extends MediaOptions
{
    use Lang;

    public function Arguments($varName = null, $description = null)
    {
        return [$varName, InputArgument::OPTIONAL, $description];
    }
}
