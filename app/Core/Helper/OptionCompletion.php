<?php

namespace Mediatag\Core\Helper;

use Mediatag\Core\MediaOptions;
use Nette\Utils\FileInfo;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder as SFinder;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Utilities\Strings as UtilitiesStrings;
use Symfony\Component\Filesystem\Filesystem;

trait OptionCompletion
{
    public function listGenre($value)
    {
        utmdump(__GENRE_LIST__);
        return __GENRE_LIST__;
    }

    public function lristFilelist($path = null)
    {

        $Filesystem     = new Filesystem();
        $CurrentPath = getcwd();
        $VideoPath = $CurrentPath ;
        $SearchPath = $VideoPath;

        if ("" != $path) {
            $SearchPath = $VideoPath . DIRECTORY_SEPARATOR . $path;
            $SearchPath = Path::normalize($SearchPath);
        //$SearchPath = "'".str_replace('\\','',$SearchPath)."'";
            if (!is_dir($SearchPath)) {
                $SearchPath =  $SearchPath . "*";
            }
        }
        utmdump($SearchPath);


        //

        // utmdump($path);

        utmdump($SearchPath);
        // UTMlog::logger('Search Directory', $path);

        $finder     = new SFinder();
        $file_array = [];
        $finder->in($SearchPath);
        $finder->depth('== 0');
        // utmdump($path ,$finder);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {

                $video_file   = $file->getRealPath();
                $video_file = str_replace($VideoPath, "", $video_file);
                utmdump($video_file);

                // $video_file = SFilesystem::makePathRelative($path,$video_file);
                // if (str_contains($video_file, '-temp-')) {
                //     $filesystem->remove($video_file);

                //     continue;
                // }
                $file_array[] = $video_file;
            }
        }

        utmdump($file_array);
        return $file_array;





    }

}
