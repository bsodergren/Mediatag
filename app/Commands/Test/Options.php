<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaOptions;
use Mediatag\Traits\Translate;

class Options extends MediaOptions
{
    use Lang;
    use Translate;
    // public $options                          = ['Default'=>false];
    // public $options = ['Default'=>true, 'Test'=>true];
    public $options = ['Default',  'Test'];
    public function Definitions()
    {
        utminfo();

        Translate::$Class = __CLASS__;

        return [
        ];
    }
}
