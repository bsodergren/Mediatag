<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Mediatag\Core\Mediatag;


use Nette\Utils\Strings;
use Nette\Utils\Callback;

use UTM\Utilities\Option;
use Nette\Utils\FileSystem;
use Mediatag\Traits\Callables;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFinder;

use Mediatag\Modules\Filesystem\MediaFilesystem;
use Symfony\Component\Process\Process as ExecProcess;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

trait Helper
{
    use Callables;
    use CaseHelper;
    use CaseHelperCreator;


    public function convert()
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);


        if (!is_array($this->ph_csv)) {
            $this->ph_csv = [$this->ph_csv];
        }

        // utmdd($this->ph_csv);
        $progressbar                  = new MediaBar(100000, 'three', 80);

        foreach ($this->ph_csv as $thisFile) {

            $currentDir      = dirname($thisFile);
            $newFileDir      = $currentDir . DIRECTORY_SEPARATOR . "new";
            FileSystem::createDir($newFileDir);

            $finishedFileDir = $currentDir . DIRECTORY_SEPARATOR . "finished";
            FileSystem::createDir($finishedFileDir);

            $filename        = basename($thisFile);

            $newFile         = $newFileDir . DIRECTORY_SEPARATOR . str_replace("csv", "txt", $filename);
            $idx             = 1;

            $progressbar->setMessage($filename)->newbar()->start();

            foreach (file($thisFile) as $input_line) {
                $s =preg_match('/.*src="([:a-z0-9\/.]+)".*\/([0-9]+)\/[a-z].*/', $input_line, $output_array);
                if ($s == true) {
                    $newline = $output_array[1] . ";" . $output_array[2] ;
                    $progressbar->advance();
                    //Mediatag::$output->writeln('<comment>' . $idx . '</comment><info>' . $newline . '</info>');
                    file_put_contents($newFile, $newline . PHP_EOL, FILE_APPEND);
                } else {
                    file_put_contents($currentDir . DIRECTORY_SEPARATOR . "fail.txt", $input_line, FILE_APPEND);
                }
                $idx++;
            }
            $renamedFile     =  $finishedFileDir . DIRECTORY_SEPARATOR . $filename;
            MediaFilesystem::renameFile($thisFile, $renamedFile);
            Mediatag::$output->writeln('<info>' . $filename . ' finished</info>');
            // $progressbar->clear();
        }

    }

}
