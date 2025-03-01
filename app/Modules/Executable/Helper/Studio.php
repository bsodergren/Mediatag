<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

class Studio
{


    public $options = [
        '-f',
        'bestvideo[width<=?1080]+bestaudio/best',
        '-o',
        __PLEX_DOWNLOAD__.'/Studios/%(uploader)s/%(title)s-%(id)s.%(ext)s',
        '--restrict-filenames',
        '-w',
        '-c',
        '--no-part',
        '--write-info-json',
        '-u',
        CONFIG['NUB_USERNAME'],
         '-p',
         CONFIG['NUB_PASSWORD'],
    ];

    public function __construct(){

    }


}
