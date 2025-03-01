<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Executable\Helper;

class Pornhub
{


    public $options = [
        '-f',
        'bestvideo[width<=?1080]+bestaudio/best',
        '-o',
        __PLEX_DOWNLOAD__.'/Pornhub/%(uploader)s/%(title)s-%(id)s.%(ext)s',
        '--restrict-filenames',
        '-w',
        '-c',
        '--no-part',
        '--write-info-json',
                '-u',
               CONFIG['PH_USERNAME'],
                '-p',
                CONFIG['PH_PASSWORD'],
    ];
    public function __construct(){

    }
}
