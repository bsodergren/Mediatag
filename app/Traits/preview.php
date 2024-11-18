<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;
use UTM\Utilities\Option;

trait preview
{
    public function preview($text, $exit = false)
    {
        // utminfo(func_get_args());

        if (Option::isTrue('preview')) {
            Mediatag::$Console->info($text);
            // $this->output->write($text);
            // if (true === $exit) {
            //     exit;
            // }
        }
    }
}
