<?php
namespace Mediatag\Modules\Executable\Helper;

use Mediatag\Modules\Executable\Callbacks\traits\CallbackCommon;
use Mediatag\Modules\Executable\Callbacks\traits\YtdlpCallBacks;
use Mediatag\Modules\Executable\Callbacks\traits\DownloadStrings;

class VideoDownloader
{

    use YtdlpCallBacks;
    use DownloadStrings;
    use CallbackCommon;
    // This is a placeholder for the VideoDownloader class.

        public $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

}