<?php

namespace Mediatag\Commands\Db\Commands\Import;

use Mediatag\Commands\Db\Commands\Export\ExportHelper;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Nette\Utils\FileSystem;
use UTM\Utilities\Option;

trait ImportHelper
{
    private $dbBackupPath = __DB_BACKUP_ROOT__ . DIRECTORY_SEPARATOR;

    private $video_file_csv = 'file.csv';

    private $video_metadata_csv = 'meta.csv';

    private $video_info_csv = 'info.csv';

    private $video_custon_csv = 'custom.csv';

    public function importMethod()
    {
        $jsonCacheDir = ExportHelper::$EXPORT_DIR . MediaFile::videoPath(__CURRENT_DIRECTORY__);
        $file_array   = (new MediaFinder)->search($jsonCacheDir, '/\.json$/i');
        $this->getJsonInfo($file_array);
        utmdd($file_array);
    }

    public function getJsonInfo($fileArray)
    {
        foreach ($fileArray as $file) {
            $video_key           = basename($file, '.info.json');
            $fileContent         = MediaFilesystem::readLines($file);
            $jsonArray           = json_decode($fileContent[0], 1);
            $jsonArray['studio'] = trim(str_replace($jsonArray['network'], '', $jsonArray['studio']), '/');

            $videoinfo  = Mediatag::$dbconn->videoExists($video_key, table: __MYSQL_VIDEO_METADATA__);
            $updateData = 'updateData';
            if (is_null($videoinfo)) {
                $exists     = Mediatag::$dbconn->videoExists($video_key, table: __MYSQL_VIDEO_FILE__);
                $updateData = 'importData';
            }
            if (! is_null($exists)) {
                $updateData = 'importData';
            } else {
                Mediatag::$output->write('update => ');
                Mediatag::$output->writeln(Mediatag::$dbconn->getLastQuery());
            }
            $this->$updateData($jsonArray, $video_key);
        }
    }

    public function updateData($data, $key)
    {
        Mediatag::$dbconn->update($data, ['video_key' => $key], __MYSQL_VIDEO_METADATA__);
    }

    public function importData($data, $key)
    {
        $data['video_key'] = $key;
        $data['Library']   = __LIBRARY__;
        $insertData        = [];
        Mediatag::$output->writeln('import =>');
        foreach ($data as $field => $value) {
            $insertData[] = [$field => $value];
        }

        Mediatag::$dbconn->insert($data, __MYSQL_VIDEO_METADATA__);
        Mediatag::$output->writeln('import => ' . Mediatag::$dbconn->getLastQuery());
    }
}
