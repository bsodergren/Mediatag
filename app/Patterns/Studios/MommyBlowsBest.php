<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Core\Mediatag;


use Mediatag\Patterns\Studios\BlowPass;

class MommyBlowsBest extends BlowPass
{
    public $studio = 'Mommy Blows Best';
    public function __construct($object){
        $classes = class_parents($this);
        $class = reset($classes);      
        $classparts = explode("\\",$class);
        $this->network = end($classparts);
          parent::__construct($object);
      }
}
