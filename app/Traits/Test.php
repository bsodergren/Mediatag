<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;
use UTM\Utilities\Option;

trait Test
{
    public function test($text, $exit = false)
    {
        utminfo(func_get_args());

        if (Option::isTrue('test')) {
            $this->output->write($text);
            if (true === $exit) {
                exit;
            }
        }
    }
}
