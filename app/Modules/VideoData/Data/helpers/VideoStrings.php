<?php 
namespace Mediatag\Modules\VideoData\Data\helpers;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\VideoData\Data\helpers\VideoCleaner;
use Mediatag\Utilities\Strings;
use UTM\Utilities\Option;
use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;


trait VideoStrings
{

    public function getText()
    {
        // utminfo(func_get_args());

        return $this->actionText; // . basename($this->video_name, '.mp4') . '.gif';// .' for '.basename($this->video_file);
    }

    public function getMessageLen($file_array)
    {
        foreach ($file_array as $file) {
            $fileLen = \strlen(basename($file, '.mp4'));
            if ($this->fileLen < $fileLen) {
                $this->fileLen = $fileLen;
            }
        }
        if ($this->fileLen > $this->maxLen) {
            $this->fileLen = $this->maxLen;
        }
    }

    public function setMessage($message, $prefix = '.mp4')
    {
        $message = basename($message, $prefix);
        $message = Strings::truncateString($message, $this->fileLen, true);
        $message = str_pad($message, $this->maxLen, ' ');

        return $message;
    }

    public function printNo($int, $space = null)
    {
        $int = str_pad($int, 4, ' ', \STR_PAD_LEFT);

        return '<comment>'.$int.'</comment> '.$space;
    }

    public function getVideoText()
    {
        // utminfo(func_get_args());

        return $this->getText().' for '.$this->setMessage($this->video_file);
    }
}