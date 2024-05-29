<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use UTM\Utilities\Option;

trait Test
{
    public function test($text, $exit = false)
    {
        if (Option::isTrue('test')) {
            $this->output->write($text);
            if (true === $exit) {
                exit;
            }
        }
    }
}
