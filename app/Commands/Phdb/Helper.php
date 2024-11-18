<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Exception;
use Nette\Utils\Callback;
use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;
use Nette\Utils\FileSystem;
use Mediatag\Traits\Callables;
use Mediatag\Utilities\Strings;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Display\MediaBar;
use Symfony\Component\Process\Process;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Symfony\Component\Process\Process as ExecProcess;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

trait Helper
{
    use Callables;
    use CaseHelperCreator;

    public function splitDb()
    {

        $callback         = Callback::check([$this, 'splitFileOutput']);

        // utminfo(func_get_args());

        if (is_array($this->ph_csv)) {
            $this->ph_csv = $this->ph_csv[0];
        }

        $rawFileDir       = __PORNHUB_CSV_DIR__;

        FileSystem::createDir($rawFileDir);

        chdir($rawFileDir);

        $command          = ["split", "--verbose", "-l", "100000", "-d", "-a2", "--additional-suffix=.csv" ,$this->ph_csv,
            "ph_db_raw_"];

        $process          = new Process($command);
        $process->setTimeout(60000);

        $this->runCommand = $process->getCommandLine();


        // UTMlog::Logger('Executing', $this->runCommand);
        // $this->preview();
        // $this->test();

        $process->start();
        $process->wait($callback);

        return true;
    }
    public function convert()
    {
        // utminfo(func_get_args());


        if (!is_array($this->ph_csv)) {
            $this->ph_csv = [$this->ph_csv];
        }

        $newFileDir                   = __PORNHUB_TXT_DIR__;

        FileSystem::createDir($newFileDir);

        $finishedFileDir              = __PORNHUB_FINISHED_DIR__;
        FileSystem::createDir($finishedFileDir);

        $progressbar                  = new MediaBar(100000, 'three', 80);

        foreach ($this->ph_csv as $thisFile) {

            $currentDir      = dirname($thisFile);

            $filename        = basename($thisFile);

            $newFile         = $newFileDir . DIRECTORY_SEPARATOR . str_replace("csv", "txt", $filename);
            $idx             = 1;

            $progressbar->setMessage($filename)->newbar()->start();

            foreach (file($thisFile) as $input_line) {
                $parts = explode("|", $input_line);
                $s     = preg_match('/.*src="([:a-z0-9\/.]+)".*\/([0-9]+)\/[a-z].*/', $input_line, $output_array);
                // utmdd($output_array);

                if ($s == true) {
                    $newline = implode("|", [$output_array[1] , $output_array[2] ,$parts[3],$parts[4],$parts[5],$parts[6] ]);
                    // utmdd($newline);
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


    public function import()
    {
        $this->dbConn                 = new Storage();
        if (!is_array($this->ph_csv)) {
            $this->ph_csv = [$this->ph_csv];
        }

        $newFileDir                   = __PORNHUB_TXT_DIR__;
        $finishedFileDir              = __PORNHUB_FINISHED_DIR__;
        $maxInsert                    = 500;
        $progressbar                  = new MediaBar(100000 / $maxInsert, 'three', 80);

        foreach ($this->ph_csv as $thisFile) {
            $this->dbConn->startTransaction();

            $currentDir      = dirname($thisFile);
            $filename        = basename($thisFile);

            $fileTmpAr       = explode("_", basename($thisFile, '.txt'));

            $fileId          = $fileTmpAr[array_key_last($fileTmpAr)];
            //   $newFile         = $newFileDir . DIRECTORY_SEPARATOR . str_replace("csv", "txt", $filename);
            $idx             = 1;
            $data            = [];
            $qidx            = 0;

            $progressbar->setMessage($filename)->newbar()->start();
            $fileContents    =            file($thisFile);
            //   $fileContents=  array_slice($fileContents, 73924+480);
            // utmdd(count($fileContents));
            foreach ($fileContents as $seKey => $input_line) {
                $array    = explode("|", $input_line);
                $urlArray = explode("/", $array[0]);
                $key      = array_key_last($urlArray);

                if ($qidx < $maxInsert) {

                    $data[]  = [
                        'file'      => $fileId ,
                        'url'       => $array[0],
                        'video_key' => $urlArray[$key],

                        'video_id'  => $array[1],

                        'title'     => Strings::clean($array[2]),
                        'genres_a'  => Strings::clean($array[3]),
                        'genres_b'  => Strings::clean($array[4]),
                        'artist'    => trim(Strings::clean($array[5])),
                    ];
                    // $data[] = $insert;
                }
                if ($qidx == $maxInsert) {
                    // utmdump([$data,$seKey]);

                    $this->insertData($data);
                    // $this->dbConn->trace();
                    // utmdump($this->dbConn->getLastError(),$this->dbConn->getLastQuery());
                    $data = [];
                    $qidx = 0;
                    $progressbar->advance();
                }
                $qidx++;
                // utmdd($id);
                $idx++;
            }
            if (count($data) > 0) {
                $this->insertData($data);

            }
            $renamedFile     =  $finishedFileDir . DIRECTORY_SEPARATOR . $filename;
            MediaFilesystem::renameFile($thisFile, $renamedFile);
            Mediatag::$output->writeln('<info>' . $filename . ' finished</info>');
            $this->dbConn->commit();
        }
    }

    private function insertData($data)
    {
        // $id = $this->dbConn->insert($data, 'mediatag_phdb');

        try {
            //     $this->dbConn->onDuplicate($updateColumns, $lastInsertId);
            $id = $this->dbConn->insertmulti($data, 'mediatag_phdb');
        } catch (Exception $e) {
            $this->dbConn->rollback();
            utmdd($e->getMessage());
            //     utmdd($e);
        }



    }

}
