<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns;

use Mediatag\Core\Mediatag;


use Mediatag\Core\MediaCache;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Pornhubdb\Helpers\CVSUtils;
use UTM\Utilities\Debug\Timer;

class Pornhub extends Patterns
{
    public const PORNHUB_PLAYLIST = __PLEX_PL_DIR__ . '/ph_playlist.txt';

    public $studio                = '';

    public function __construct($object)
    {
        utminfo();

        $video_details   = false;
        //        $dbConn          = new PornhubDB();
        include_once __DATA_MAPS__ . '/filemap.php';

        parent::__construct($object);
        $this->video_key = $object->video_key;
        if (str_starts_with($this->video_key, 'x')) {
            if (str_contains($object->video_name, '000K')) {
                // Mediatag::$output->writeln('Looking for '.$object->video_name);

                // $video_details = MediaCache::get($object->video_name);
                if (false === $video_details) {
                    $peices = explode('_', basename($object->video_name, '.mp4'));
                    $a      = array_reverse($peices);
                    //   $a[0] = '100000752';
                   
                    $file   = getVideoCSV($a[0]);
                   
                    if (null !== $file) {
                        // Mediatag::$output->writeln($object->video_name.' in '.$file);
                        $csv_line = $this->findLine($file, $a[0]);
                        if (null !== $csv_line) {
                            $video_details = CVSUtils::toArray($csv_line);
                            // file_put_contents(self::PORNHUB_PLAYLIST, $video_details['video_url'].\PHP_EOL, \FILE_APPEND);
                            // $r             = MediaCache::put($object->video_name, $video_details);
                        }
                        // $r = MediaCache::put($object->video_name, []);
                    }
                    // $r = MediaCache::put($object->video_name, []);
                }
                if (\is_array($video_details)) {
                    $this->videoInfo = $video_details;
                }

                // $array  = MediaCache::get($object->video_name);
                // if (false === $array) {
                //     // Mediatag::$output->writeln('Running Query');
                //     // Mediatag::$output->writeln($sql);
                //     //  $array    = $dbConn->query($sql);
                //     //  $array[1] = '1';
                //     //   $r        = MediaCache::put($object->video_name, $array);
                // }

                // if (is_array($video_details)) {
                // if (array_key_exists('video_url', $video_details)) {
                //         if (array_key_exists('video_url', $arravideo_urly[0])) {
                //             $this->videoInfo = $video_url[0];
                //             //  Mediatag::$output->writeln('Writing to Playlist');
                // file_put_contents(self::PORNHUB_PLAYLIST, $video_details['video_url'].\PHP_EOL, \FILE_APPEND);
                // }
                //     }
                // }
            }
        }
    }

    public function findLine($file, $key)
    {
        utminfo();

        //  $csv_file = __NEW_CSV_DIR__.'/'.$file.'.csv';

        //   return shell_exec("grep -w {$key} {$csv_file}");
    }

    private function getDataTag($tag)
    {
        utminfo();

        $value = '';
        if (null === $this->videoInfo) {
            return false;
        }
        if (!\array_key_exists($tag, $this->videoInfo)) {
            return false;
        }
        if ('video_artist' == $tag || 'genres_a' == $tag || 'genres_b' == $tag) {
            $value = str_replace(';', ',', $this->videoInfo[$tag]);
        } else {
            $value = $this->videoInfo[$tag];
        }

        if ('' == $value) {
            return false;
        }

        return $value;
    }

    public function getArtist()
    {
        utminfo();

        return $this->getDataTag('video_artist');
    }

    public function getStudio() {}

    public function getGenre()
    {
        utminfo();

        return $this->getDataTag('genres_a') . ',' . $this->getDataTag('genres_b');
    }

    public function getTitle($names = null)
    {
        utminfo();

        return $this->getDataTag('video_title');
    }
}
