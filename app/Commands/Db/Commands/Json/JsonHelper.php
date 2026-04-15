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
    public $dbConn;

    public function JsonExec()
    {
        // $this->allDbFiles = parent::$dbconn->getAllDbFiles();

        // $this->dbConn = Storage::$DB;
        // utmdd(get_class_methods(get_class(parent::$dbconn)));

        // utmdd($this->file_array);
        // $query = parent::$dbconn->querybuilder('select', "CONCAT(fullpath,'/',filename) as file_name,fullpath, video_key");
        // $results = $this->dbConn->query($query);
        // foreach ($results as $key => $arr) {
        //     if ($arr['fullpath'] === null) {
        //         continue;
        //     }
        //     $fileListArray[$arr['video_key']] = $arr['file_name'];
        // }
        // $this->file_array = $fileListArray;

        // $this->file_array = (new MediaFinder)->search(getcwd(), '/\.mp4$/i');
        if (Option::istrue('update')) {
            $this->file_array = parent::$dbconn->getDbFileList(' AND updatedJson = 1');
            parent::$output->writeln('<info> update Json</info>');
            $this->setJson();
        } else {
            $this->file_array = parent::$dbconn->getDbFileList(' AND (updatedJson = 0 or updatedJson is null)');
            parent::$output->writeln('<info> get new json file </info>');
            $this->getJson();
        }

        return 1;
    }

    public function setJson()
    {
        $this->file_keys = $this->searchDownloads('json');

        $count = count($this->file_keys);
        // utmdump($count);
        // utmdd($this->file_array);
        foreach ($this->file_keys as $key) {
            $id = '<info>' . $count . '</>';
            $count--;
            if (array_key_exists($key, $this->file_array)) {
                $file      = $this->file_array[$key];
                $videoInfo = (new File($file))->get();
                $json_key  = File::getVideoKey($file, 'Pornhub');
                if (! str_starts_with($json_key, 'x')) {
                    $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
                    if (Mediatag::$filesystem->exists($json_file)) {
                        if (filesize($json_file) > 1024) {
                            $reader = new Reader($videoInfo);

                            $actionTags = $reader->actionTags();
                            // utmdump([$json_file, filesize($json_file), $actionTags]);

                            if (count($actionTags) > 0) {
                                if (! is_null($actionTags['actiontags'])) {
                                    // parent::$output->writeln('<info> Found  actiontags, updating video </info>');
                                    $this->updateVideoMarkers($videoInfo, $actionTags['actiontags'], $id);
                                    parent::$dbconn->updatedJson($json_key, 2);

                                    continue;
                                }
                            }

                            parent::$output->writeln($id . '<error> No  actiontags in json file </>');
                        }
                    }
                }
            }
        }
    }

    public function getJson()
    {
        // utminfo(func_get_args());

        $count = count($this->file_array);
        foreach ($this->file_array as $json_key => $file) {
            $backupFile = '';
            //$json_key = File::getVideoKey($file, 'Pornhub');

            // utmdump([$file, $json_key]);
            if (! str_starts_with($json_key, 'x')) {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';

                // utmdd($json_file, Mediatag::$filesystem->exists($json_file));

                // if (Mediatag::$filesystem->exists($json_file)) {
                //     // utmdump(['json file exists' => $json_file]);
                //     if (filesize($json_file) < 1024) {
                //         // utmdump(['delete file file' => $json_file]);
                //         MediaFilesystem::delete($json_file);
                //     } else {
                //         $backupFile = __JSON_CACHE_DIR__ . '/prev/' . $json_key . '.info.json';
                //         if (! Mediatag::$filesystem->exists($backupFile)) {
                //             MediaFilesystem::renameFile($json_file, $backupFile, false);
                //         }
                //         // utmdump(['backupFile file' => $backupFile]);
                //     }
                // }

                if (Mediatag::$filesystem->exists($json_file)) {
                    $data = file_get_contents($json_file);
                    if (\str_contains($data, 'actionTags')) {
                        $jsondata = \json_decode($data, true);

                        if ($jsondata['actionTags'] != '') {
                            parent::$output->writeln('<info>' . $jsondata['actionTags'] . ' ' . basename($json_file) . ' </info>');
                        }
                    }

                    // $ytdl   = (new Youtube)->run('');
                    // $return = $ytdl->youtubeGetJson($json_key);

                    // if (is_null($return)) {
                    //     if (Mediatag::$filesystem->exists($backupFile)) {
                    //         MediaFilesystem::renameFile($backupFile, $json_file);
                    //     }
                    //     parent::$output->writeln('<error>' . $count . ' : ' . $ytdl->yt_error_string . ' </error>');
                    // } elseif (Mediatag::$filesystem->exists($json_file)) {
                    //     parent::$output->writeln('<info>' . $count . ' adding json ' . basename($json_file) . ' </info>');
                    // } else {
                    //     parent::$output->writeln('<error>' . $count . ' : ' . $ytdl->yt_error_string . ' </error>');
                    //     MediaFilesystem::writeFile($json_file, '{"id": "' . $json_key . '", "error":"' . $ytdl->yt_error_string . '"}', false);
                    // }
                } else {
                    parent::$output->writeln('<id>json file for ' . basename($file) . ' exists</id>');
                }
                parent::$dbconn->updatedJson($json_key, 1);
            } else {
                parent::$output->writeln('<comment>' . $count . 'skipping ' . basename($file) . ' </comment>');
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
        $dbConn  = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);

        foreach ($markers as $marker) {
            $parts = explode(':', $marker);
            $data  = [
                'timeCode'   => round($parts[1], 0),
                'video_id'   => $video_id,
                'markerText' => $parts[0] . '_Start',
            ];
            $dbConn->where('timeCode', round($parts[1], 0));
            $dbConn->where('markerText', $parts[0] . '_Start');
            $dbConn->where('video_id', $video_id);
            $res = $dbConn->getone(__MYSQL_VIDEO_CHAPTER__);

            // utmdump([$dbConn->getLastQuery(), $res]);
            if (is_null($res)) {
                parent::$output->writeln($id . '<id>Updating markers  for ' . basename($videoInfo['video_file']) . '</id>');
                $dbConn->insert(__MYSQL_VIDEO_CHAPTER__, $data);
                // utmdump($dbConn->getLastQuery());
            }
        }

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
