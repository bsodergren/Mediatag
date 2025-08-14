<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\TagBuilder\Patterns;
use Mediatag\Modules\TagBuilder\TagReader;

use function array_key_exists;
use function dirname;

use const DIRECTORY_SEPARATOR;

class PrivateVid extends Patterns
{
    public $studio = 'Private';

    public $regex = [
        'privatevid' => [
            'artist' => [
                'pattern'             => '/([a-zA-Z0-9_]*)-?([a-zA-Z0-9_]*)-?([A-Za-z]{3}[0-9]{3}_[sS0-9]{1,3}_.*.mp4)/i',
                'delim'               => '_And_',
                'match'               => 2,
                'artistFirstNameOnly' => false,
            ],
            'title'  => [
                'pattern' => '/([a-zA-Z0-9_]*)-?([a-zA-Z0-9_]*)-?([A-Za-z]{3}[0-9]{3}_[sS0-9]{1,3}_.*.mp4)/i',
                'match'   => 1,
                'delim'   => '_',
            ],
            'studio' => [
                'pattern' => false,
            ],
        ],
    ];

    public function getFilename($file)
    {
        // utminfo(func_get_args());
        $videoData = new MediaFile($file);
        $path      = dirname($file);
        $filename  = basename($file);

        $dbData = new TagReader();
        $tags   = $dbData->loadVideo($videoData->get())->getDbValues();
        // utmdd(__LINE__,$tags ,$videoData->get());
        if (null !== $tags) {
            if (array_key_exists('title', $tags)) {
                $artist = '';
                $title  = $tags['title'];
                if (array_key_exists('artist', $tags)) {
                    $artist = $tags['artist'];
                    $artist = str_replace(' ', '_', $artist);

                    $artist = str_replace(',', '_AND_', $artist).'-';
                    $artist = str_replace('__', '_', $artist);
                }
                $title = str_replace(' ', '_', $title);

                if (str_contains($filename, $title)) {
                    // utmdd(__LINE__,$file);

                    return $file;
                }

                preg_match($this->regex['privatevid']['title']['pattern'], $filename, $output_array);

                $filename = $title.'-'.$artist.$output_array[3];

                $file = $path.DIRECTORY_SEPARATOR.$filename;
            }
        }

        // utmdd(__LINE__,$file);

        return $file;
        //  $title = get_class_vars(get_class($dbData));
    }
}
