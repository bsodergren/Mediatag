<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

trait Network
{
    /**
     * getStudio.
     */
    public function metaNetwork()
    {
        // utminfo(func_get_args());

        if (null === $this->network) {
            $class = get_parent_class($this);

            $obj           = new $class();
            $this->network = $obj->network;
        }

        return $this->network;
    }
}
