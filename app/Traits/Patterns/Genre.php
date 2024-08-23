<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Core\Mediatag;

trait Genre
{
    /**
     * getGenre.
     */
    public function getGenre()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        if ('' == $this->genre) {
            $filename = $this->video_file;
            $success  = preg_match(__GENRE_REGEX__, $filename, $matches);
            if (true == $success) {
                $this->genre = $matches[1];
            }
        }

        return $this->genre;
    }
}
