<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Modules\TagBuilder\TagReader;

trait Network
{
    /**
     * getStudio.
     */
    public function metaNetwork()
    {
        // utminfo(func_get_args());

        if ($this->studio === null) {
            return null;
        }
        if ($this->network === null) {
            $class = get_parent_class($this);
            $obj   = new $class($this->video_key, new TagReader);

            $this->network = $obj->network;
        }

        return $this->network;
    }
}
