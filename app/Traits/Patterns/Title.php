<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Core\Mediatag;


use Mediatag\Modules\Executable\JsExec;
use Mediatag\Modules\Filesystem\MediaFile;
use UTM\Bundle\Monolog\UTMLog;

trait Title
{
    /**
     * getTitleRegex.
     */
    public function getTitleRegex()
    {
        utminfo(func_get_args());

        return $this->getKeyValue('title', 'pattern');
    }

    /**
     * gettitleMatch.
     */
    public function gettitleMatch()
    {
        utminfo(func_get_args());

        return $this->getKeyValue('title', 'match');
    }

    /**
     * getTitleDelim.
     */
    public function getTitleDelim()
    {
        utminfo(func_get_args());

        return $this->getKeyValue('title', 'delim');
    }

    /**
     * getTitle.
     */
    public function getTitle()
    {
        utminfo(func_get_args());

        $regex = $this->getTitleRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);
            if (0 != $success) {
                if (! \array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }
                $video_key = MediaFile::getVideoKey($this->video_name);

                $title     = $output_array[$this->gettitleMatch()];
                $title     = str_replace('_s_', 's_', $title);
                $title     = str_replace($this->getTitleDelim(), ' ', $title);
                $pretitle  = $title;
                $title     = (new JsExec($video_key))->read($title);
                // UTMlog::Logger('Title Tag', [$pretitle,$title]);



                /*
                                foreach (BASIC_WORD_MAP as $find => $replace) {
                                    $prev_title = $title;
                                    // $title = preg_replace('/(.*)\b('.$find .')(.*)/i', '$1'.$replace.'$3', $title);
                                    $title      = str_replace($find, $replace, $title);
                                    if ($prev_title != $title) {

                                    }
                                }
                */
                /*
                                foreach (WORD_MAP as $find => $replace) {
                                    $prev_title = $title;
                                    $title      = preg_replace('/(.*)\b('.$find.')(.*)/i', '$1'.$replace.'$3', $title);
                                    //                    $title = str_replace($find, $replace, $title);
                                    if ($prev_title != $title) {

                                    }
                                }
                                $parts = preg_split('/(?=[A-Z])/', $title, -1, \PREG_SPLIT_NO_EMPTY);
                                $title = implode(' ', $parts);
                                $title = preg_replace('/([0-9]+)/', ' $1', $title);
                                $title = str_replace('', ' ', $title);
                                $title = str_replace(' -', '-', $title);
                                $title = str_replace('  ', ' ', $title);
                */

                return str_replace('- ', '-', $title);
            }
        }

        return false;
    }
}
