<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Db\Commands\Import;

use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

use const DIRECTORY_SEPARATOR;

trait Helper
{
    private $dbBackupPath       = __DB_BACKUP_ROOT__.DIRECTORY_SEPARATOR;
    private $video_file_csv     = 'file.csv';
    private $video_metadata_csv = 'meta.csv';
    private $video_info_csv     = 'info.csv';
    private $video_custon_csv   = 'custom.csv';

    public function execImport()
    {
        // $this->dbBackupPath = __DB_BACKUP_ROOT__;
        if (Option::isTrue('library')) {
            $this->dbBackupPath = $this->dbBackupPath.__LIBRARY__.DIRECTORY_SEPARATOR;
        }

        FileSystem::createDir($this->dbBackupPath);

        $this->runImport(__MYSQL_VIDEO_FILE__, $this->video_file_csv);
        $this->runImport(__MYSQL_VIDEO_METADATA__, $this->video_metadata_csv);
        $this->runImport(__MYSQL_VIDEO_INFO__, $this->video_info_csv);
        // $this->runImport(__MYSQL_VIDEO_CUSTOM__,$this->video_custon_csv);
    }

    private function runImport($table, $csv_file)
    {
        $csv_file = $this->dbBackupPath.$csv_file;

        $fp = fopen($csv_file, 'w');

        fclose($fp);
    }
}
