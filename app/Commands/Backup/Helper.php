<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Backup;

use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Nette\Utils\FileSystem as NetteFile;

use Nette\Utils\Callback;
use Symfony\Component\Process\Process as ExecProcess;
use UTM\Utilities\Option;

trait Helper
{


    private function mysqlDump($options,$backupFile)
    {

        $baseCommand = [
            'mysqldump',
            '-h',
            'localhost',
            '-u',
            __SQL_USER__,
            '-p'.__SQL_PASSWD__,
            '--compact'
        ];

        $fileoutputCmd = [
            '-r',
            $backupFile,
        ];


        $command     = array_merge($baseCommand, $options,$fileoutputCmd);

        $process = new ExecProcess($command);
        $process->setTimeout(60000);
        $process->run();
        $res = $process->getOutput();

        unset($process);
    }



    public function backupDb()
    {

        if (! is_dir($this->backupDirectory)) {
            NetteFile::createdir($this->backupDirectory);

        }

        $backupDbFile = $this->backupDirectory. DIRECTORY_SEPARATOR . __MYSQL_DATABASE__.".sql";

        $this->mysqlDump(['-d',__MYSQL_DATABASE__],$backupDbFile);
        $this->backupFuncDb();

        $defines = get_defined_constants(true);
        foreach($defines['user'] as $key => $value) {
            if(str_contains($key, "TABLE__")) {
                $tables[] = $value;
            }
        }
        foreach($tables as $tableName) {
            $this->backupTable($tableName);
        }

    }

    public function backupTable($tableName)
    {

        $backupDbFile = $this->backupDirectory. DIRECTORY_SEPARATOR . $tableName .".sql";

        $this->mysqlDump(['--skip-extended-insert',__MYSQL_DATABASE__, $tableName],$backupDbFile);
    }

    public function backupFuncDb()
    {
        $backupDbFile = $this->backupDirectory. DIRECTORY_SEPARATOR . "function.sql";
        $this->mysqlDump(['--skip-triggers','--routines','--no-create-info','--no-data','--no-create-db','--skip-opt',__MYSQL_DATABASE__],$backupDbFile);

    }

    public function sortDirectory($options = [])
    {
        $dir_array = [];
        $files = 0;
        // $arr= array_unique($this->video_array[$key]);
        if (Option::isTrue('backup')) {
            $key = Option::getValue('backup');
        }

        if (\array_key_exists($key, $this->video_array)) {
            $arr = array_unique($this->video_array[$key]);
            foreach ($arr as $n => $video_path) {
                if (str_contains($video_path, $options)) {
                    $dir_array[] = $video_path;
                }
            }
            $this->video_array[$key] = $dir_array;
            $files = \count($this->video_array[$key]);
        }

        if (0 == $files) {
            utmdd([__METHOD__, 'No directory', $files]);
        }
    }

    public function print()
    {
    }

    public function backupStudio($key)
    {
        $home = '/home/bjorn/plex/XXX';
        $path = '/media/backup/home/plex/XXX';
        if (\array_key_exists($key, $this->video_array)) {
            $files = \count($this->video_array[$key]);

            echo "Rsyncing {$files}".\PHP_EOL;

            $arr = array_unique($this->video_array[$key]);
            foreach ($arr as $n => $video_path) {
                $newPath = str_replace($home, $path, $video_path);
                // $newFile = $newPath.DIRECTORY_SEPARATORvideo_pathvideo['video_name'];

                if (! is_dir($newPath)) {
                    FileSystem::createdir($newPath);
                }

                echo $video_path.' '.$newPath.\PHP_EOL;
                $this->rsync($video_path.'/', $newPath.'/');
            }
        }
    }

    public function sort()
    {
        foreach ($this->VideoList['file'] as $key => $videoArray) {
            if (str_starts_with($key, 'x')) {
                $this->video_array['studio'][] = $videoArray['video_path'];
            } else {
                $this->video_array['ph'][] = $videoArray;
            }
        }
    }

    public function backupPh()
    {
        $this->backupStudio('ph');
    }

    public function rsync($old, $new)
    {
        $command = [
            'rsync',
            '--progress',
            '--update',
            '-r',
            '--remove-source-files',

            $old,
            $new,
        ];

        $callback = Callback::check([$this, 'Output']);

        $process = new ExecProcess($command);
        $process->setTimeout(60000);
        //  utmdd([__METHOD__,$process->getcommandline()]);
        $process->start();
        $process->wait($callback);
    }
}
