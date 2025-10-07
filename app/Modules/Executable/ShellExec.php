<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable;

use Mediatag\Core\MediaCache;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Callbacks\ProcessCallbacks;
use Nette\Utils\Callback;

class ShellExec extends MediatagExec
{
    use ProcessCallbacks;

    public $execMode;

    public function __construct()
    {
        // utminfo($videoData);
        parent::__construct();
    }

    public function mediaDb($test=false)
    {
        $callback = Callback::check([$this, 'Output']);
$exec = "exec";
        if($test === true){
            $exec = "testexec";
        }
        $this->$exec(['mediadb', '--path', getcwd()], $callback);
        $this->$exec(['mediadb', '--path', getcwd(), 'all'], $callback);
    }
}
