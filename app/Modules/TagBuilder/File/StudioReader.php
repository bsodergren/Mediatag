<?php

namespace Mediatag\Modules\TagBuilder\File;

use Symfony\Component\Filesystem\Filesystem;
use Mediatag\Modules\TagBuilder\Json\Reader as jsonReader;

trait StudioReader
{
    private function studioParse()
    {
        $studio_dir   = (new FileSystem())->makePathRelative($this->video_path, __PLEX_HOME__ . '/' . __LIBRARY__);
        $studio_dir   = str_replace('/' . $this->getGenre() . '/', '', $studio_dir);
        $arr          = explode('/', $studio_dir);

        foreach ($arr as $idx => $studio_string) {
            foreach (__SKIP_STUDIOS__ as $k) {
                if ($studio_string == $k) {
                    unset($studio_array[$idx]);

                    continue 2;
                }
                $studio_array[$idx] = $studio_string;
            }
        }

        return implode('/', $studio_array);
        ;

    }
    private function isPhFile()
    {

        $json = new jsonReader($this->videoData);
        $return = $json->getTagArray();

        if (count($return) > 0) {
            if (array_key_exists('studio', $return)) {
                $this->studio = $return['studio'];
            }
        }
        // 

        if (null === $this->studio) {
            $string = $this->studioParse();
            $studio_array = explode('/', $string);
            // utmdd(["studio_array",$studio_array]);

            if (null !== $studio_array[0]) {

                $this->studio = $studio_array[0];
                if (array_key_exists('1', $studio_array)) {
                    $this->studio = $studio_array[1];


                }
                $this->network = "Pornhub";
            }
        }
// utmdd([$this->network , $this->studio ]);
    }
    private function notPhFile()
    {

        $studio_dir = $this->studioParse();
        $studio = '';
        if ('' != $studio_dir) {
            $studio_dir = '/' . $studio_dir;
            $studio_dir = str_replace('//', '/', $studio_dir);
        }

        $success    = preg_match('/\/([\w& ]+)\/?([\w\W]+)?/i', $studio_dir, $matches);

        // UTMlog::Logger('File Studio Dir', $matches);
        if (true == $success) {
            if (\array_key_exists(2, $matches)) {
                $network               = $matches[1];
                $studio                = $matches[2];
                foreach (__SKIP_STUDIOS__ as $k) {
                    if ($studio == $k) {
                        $studio = null;
                    }                 
                }

            } else {
                $studio = $matches[1];
                if ('' != $studio) {
                    foreach (__SKIP_STUDIOS__ as $k) {
                        if ($studio == $k) {
                            $studio = null;
                        }
                    }
                }
            }


        }
        $this->studio  = $studio;
    }

}
