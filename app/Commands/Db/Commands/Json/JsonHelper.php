<?php

namespace Mediatag\Commands\Db\Commands\Json;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\TagBuilder\Json\Reader;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use Mediatag\Utilities\Strings;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

trait JsonHelper
{
    use StudioJsonHelper;

    public $dbConn;

    public function JsonExec()
    {
        if (Option::istrue('update')) {
            parent::$output->writeln('<info> update Json</info>');

            $this->file_array = Storage::$DB->getDbFileList(' AND updatedJson = 1');
            $this->setJson();
        } else {
            $this->file_array = Storage::$DB->getDbFileList(' AND (updatedJson = 0 or updatedJson is null)');
            parent::$output->writeln('<info> get new json file </info>');
            $this->getJson();

            // $this->file_array = Storage::$DB->getDbFileList(' AND updatedJson = 1');
            // $this->setJson();
        }

        return 1;
    }

    private function getJsonFilelist()
    {
        // $filearray = [];
        // utmdump($this->file_array);
        foreach ($this->file_array as $json_key => $file) {
            $backupFile = '';
            if (str_starts_with($json_key, 'x')) {
                $json_file = __STUDIO_JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
            } else {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
            }

            if (\file_exists($json_file)) {
                $json_file = Reader::checkJsonForUpdate($json_file, $json_key);

                $filearray[$json_key] = ['file' => $file, 'json' => $json_file];
            }
        }
        // utmdump($filearray);

        return $filearray;
    }

    public function setJson()
    {
        $jsonFileList = $this->getJsonFilelist();

        $count = count($jsonFileList);
        foreach ($jsonFileList as $json_key => $file) {
            $json_file  = $file['json'];
            $video_file = $file['file'];
            $id         = '<info>' . $count . '</>';
            $count--;

            $videoInfo  = (new File($video_file))->get();
            $reader     = new Reader($videoInfo);
            $actionTags = $reader->actionTags();
            // utmdump([$json_file, filesize($json_file), $actionTags]);
            // utmdump($actionTags, $videoInfo);

            if (count($actionTags) > 0) {
                if (! is_null($actionTags['actiontags'])) {
                    parent::$output->writeln('<info> Found  actiontags, updating video </info>');
                    $this->updateVideoMarkers($videoInfo, $actionTags['actiontags'], $id);
                    Storage::$DB->updatedJson($json_key, 2);

                    continue;
                }
            }

            // parent::$output->writeln($id . '<error> No  actiontags in json file </>');
        }
    }

    public function getJson()
    {
        // utminfo(func_get_args());
        $jsonFileList = $this->getJsonFilelist();
        $count        = count($jsonFileList);
        foreach ($jsonFileList as $json_key => $file) {
            $json_file  = $file['json'];
            $video_file = $file['file'];
            $backupFile = '';

            $data = file_get_contents($json_file);
            if (\str_contains($data, 'actionTags')) {
                $jsondata = \json_decode($data, true);
                if ($jsondata['actionTags'] != '') {
                    parent::$output->writeln('<info>' . $jsondata['actionTags'] . ' ' . basename($json_file) . ' </info>');
                    Storage::$DB->updatedJson($json_key, 1);
                }
            }

            $count--;
        }
    }

    private function updateVideoMarkers($videoInfo, $markerArray, $id)
    {
        if (is_null($markerArray)) {
            return false;
        }

        $video_id = (new Markers)->getvideoId($videoInfo['video_key']);

        $markers = explode(',', $markerArray);
        $dbConn  = MysqliDb::getInstance();
        $total   = 0;
        foreach ($markers as $marker) {
            $parts = explode(':', $marker);
            $data  = [
                'timeCode'   => round($parts[1], 0),
                'video_id'   => $video_id,
                'markerText' => $parts[0],
            ];
            $dbConn->where('timeCode', round($parts[1], 0));
            $dbConn->where('markerText', $parts[0]);
            $dbConn->where('video_id', $video_id);
            $res = $dbConn->getone(__MYSQL_VIDEO_MARKERS__);

            if (is_null($res)) {
                $dbConn->insert(__MYSQL_VIDEO_MARKERS__, $data);
                $total++;
            }
        }
        parent::$output->writeln($id . ' <id>Added ' . $total . ' tags for ' . basename($videoInfo['video_file']) . '</id>');

        //
        // utmdd($video_id, $markerArray);
    }

    private function searchDownloads($type = 'json')
    {
        $fileArray = [];
        switch ($type) {
            case 'json':
                $search_params = 'info.json';
                $desc          = 'Json ';
                break;
            case 'srt':
                $search_params = 'en.srt';
                $desc          = 'Caption ';
                break;
        }
        MediaFinder::$depth = 1;
        $file_array         = Mediatag::$finder->Search(__JSON_CACHE_DIR__, '*.' . $search_params, exit: false);

        if ($file_array === null) {
            return null;
        }
        foreach ($file_array as $file) {
            $first = Strings::after($file, __JSON_CACHE_DIR__ . '/');

            $key = Strings::before($first, '.');

            $fileArray[] = $key;
        }

        //
        return $fileArray;
    }
}
