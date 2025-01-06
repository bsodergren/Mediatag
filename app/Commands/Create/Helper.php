<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Create;

use UTM\Utilities\Option;

trait Helper
{
 public function createCommand(){
    utmdd([__METHOD__,Option::getOptions()]);
    
 }
 public function addCommand(){
    utmdd([__METHOD__,Option::getOptions()]);
    
 }
}
