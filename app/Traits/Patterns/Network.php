<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Core\Mediatag;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\Debug;

trait Network
{
    /**
     * getStudio.
     */
    public function metaNetwork()
    {
        utminfo();
        utmdump($this->network);

        if ($this->network === null) {
            $class = get_parent_class($this);
            
            $obj = new $class();
            $this->network = $obj->network;

        }
        utmdump($this->network);


        return $this->network;

    }

}
