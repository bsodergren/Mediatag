<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

trait Genre
{
    /**
     * getGenre.
     */
    public function getGenre()
    {
        // utminfo(func_get_args());

        if ($this->genre == '') {
            $filename = $this->video_file;
            $success  = preg_match(__GENRE_REGEX__, $filename, $matches);
            if ($success == true) {
                $this->genre = $matches[1];
            }
        }

        return $this->genre;
    }
}
