<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\VideoInfo\helpers;

use Mediatag\Utilities\Strings;

trait VideoStrings
{
    public $actionText = '';

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


    private function getTableField(){
        $thumbType = $this->thumbType;
        if ('markers' == $this->thumbType) {
            $thumbType = 'thumbnail';
        }      
        return $thumbType;
    }
}
