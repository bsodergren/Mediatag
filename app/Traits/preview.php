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
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if (Option::isTrue('preview')) {
            $this->output->write($text);
            if (true === $exit) {
                exit;
            }
        }
    }
}
