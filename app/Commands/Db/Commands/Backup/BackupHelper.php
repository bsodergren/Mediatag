<?php

namespace Mediatag\Commands\Db\Commands\Backup;

use const DIRECTORY_SEPARATOR;

use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

trait BackupHelper
{
    private $dbBackupPath = __DB_BACKUP_ROOT__ . DIRECTORY_SEPARATOR;

    private $video_file_csv = 'file.csv';

    private $video_metadata_csv = 'meta.csv';

    private $video_info_csv = 'info.csv';

    private $video_custon_csv = 'custom.csv';

    public function backupMethod()
    {
        if (Option::isTrue('library')) {
            $this->dbBackupPath = $this->dbBackupPath . __LIBRARY__ . DIRECTORY_SEPARATOR;
        }

        FileSystem::createDir($this->dbBackupPath);

        $this->doBackup(__MYSQL_VIDEO_FILE__, $this->video_file_csv);
        $this->doBackup(__MYSQL_VIDEO_METADATA__, $this->video_metadata_csv);
        $this->doBackup(__MYSQL_VIDEO_INFO__, $this->video_info_csv);
        // $this->doBackup(__MYSQL_VIDEO_CUSTOM__,$this->video_custon_csv);
    }

    private function doBackup($table, $csv_file)
    {
        $csv_file = $this->dbBackupPath . $csv_file;

        if (file_exists($csv_file)) {
            unlink($csv_file);
        }
        $fp = fopen($csv_file, 'w');

        $results = $this->getResults($table);
        foreach ($results as $i => $row) {
            // utmdd($row);
            unset($row['id']);
            unset($row['added']);
            unset($row['last_updated']);
            unset($row['new']);

            if ($i == 0) {
                $keys = array_keys($row);
                fputcsv($fp, $keys, ',', '"', '');
            }
            fputcsv($fp, $row, ',', '"', '');
        }
        fclose($fp);
    }

    private function getResults($table)
    {
        $db = parent::$Storage->dbConn;

        if (Option::isTrue('library')) {
            if (! str_contains($table, 'mediatag_video_custom')) {
                $db->where('Library', __LIBRARY__);
            } else {
                $query = 'SELECT c.* FROM mediatag_video_file as f,mediatag_video_custom as c WHERE f.video_key = c.video_key and f.Library = "' . __LIBRARY__ . '"';

                return $db->rawQuery($query);
            }
        }

        return $db->get($table);
    }
}
