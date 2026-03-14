<?php

namespace Mediatag\Commands\Db\Commands\Json;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFile as File;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\TagBuilder\Json\Reader;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\Section\VideoFileInfo;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;

trait JsonHelper
{
    public $dbConn;

    public function JsonExec()
    {
        $this->dbConn     = new MysqliDb('localhost', __SQL_USER__, __SQL_PASSWD__, __MYSQL_DATABASE__);
        $this->file_array = (new MediaFinder)->search(getcwd(), '/\.mp4$/i');
        if (Option::istrue('update')) {
            parent::$output->writeln('<info> update Json</info>');
            $this->setJson();
        } else {
            parent::$output->writeln('<info> get new json file </info>');
            $this->getJson();
        }

        return 1;
    }

    public function setJson()
    {
        $count = count($this->file_array);
        foreach ($this->file_array as $k => $file) {
            $videoInfo = (new File($file))->get();
            $json_key  = File::getVideoKey($file, 'Pornhub');
            if (! str_starts_with($json_key, 'x')) {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';

                if (Mediatag::$filesystem->exists($json_file)) {
                    if (filesize($json_file) > 1024) {
                        $reader     = new Reader($videoInfo);
                        $actionTags = $reader->actionTags();
                        // utmdump([$json_file,filesize($json_file),$actionTags]);

                        if (count($actionTags) > 0) {
                            if (! is_null($actionTags['actiontags'])) {
                                parent::$output->writeln('<info> Found  actiontags, updating video </info>');
                                $this->updateVideoMarkers($videoInfo, $actionTags['actiontags']);

                                continue;
                            }
                        }

                        parent::$output->writeln('<error> No  actiontags in json file </>');
                    }
                }
            }
        }
    }

    public function getJson()
    {
        // utminfo(func_get_args());

        $count = count($this->file_array);
        foreach ($this->file_array as $k => $file) {
            $json_key = File::getVideoKey($file, 'Pornhub');

            if (! str_starts_with($json_key, 'x')) {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
                // utmdd($json_file, Mediatag::$filesystem->exists($json_file));

                if (Mediatag::$filesystem->exists($json_file)) {
                    utmdump(['json file exists' => $json_file]);
                    if (filesize($json_file) < 1024) {
                        utmdump(['delete file file' => $json_file]);
                        MediaFilesystem::delete($json_file);
                    } else {
                        $backupFile = __JSON_CACHE_DIR__ . '/prev/' . $json_key . '.info.json';
                        MediaFilesystem::renameFile($json_file, $backupFile, false);
                        utmdump(['backupFile file' => $backupFile]);
                    }
                }
                if (! Mediatag::$filesystem->exists($json_file)) {
                    $ytdl   = (new Youtube)->run('');
                    $return = $ytdl->youtubeGetJson($json_key);

                    if (is_null($return)) {
                        if (Mediatag::$filesystem->exists($backupFile)) {
                            MediaFilesystem::renameFile($backupFile, $json_file);
                        }
                        parent::$output->writeln('<error>' . $count . ' : ' . $ytdl->yt_error_string . ' </error>');
                    } elseif (Mediatag::$filesystem->exists($json_file)) {
                        parent::$output->writeln('<info>' . $count . ' adding json ' . basename($json_file) . ' </info>');
                    } else {
                        parent::$output->writeln('<error>' . $count . ' : ' . $ytdl->yt_error_string . ' </error>');
                        MediaFilesystem::writeFile($json_file, '{"id": "' . $json_key . '", "error":"' . $ytdl->yt_error_string . '"}', false);
                    }
                } else {
                    parent::$output->writeln('<id>json file for ' . basename($file) . ' exists</id>');
                }
            } else {
                parent::$output->writeln('<comment>' . $count . 'skipping ' . basename($file) . ' </comment>');
            }
            $count--;
        }
    }

    private function updateVideoMarkers($videoInfo, $markerArray)
    {
        if (is_null($markerArray)) {
            return false;
        }
        $video_id = (new Markers)->getvideoId($videoInfo['video_key']);

        $markers = explode(',', $markerArray);
        $dbConn  = $this->dbConn;

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
                parent::$output->writeln('<id>Updating markers  for ' . basename($videoInfo['video_file']) . '</id>');
                $dbConn->insert(__MYSQL_VIDEO_CHAPTER__, $data);
                utmdump($dbConn->getLastQuery());
            }
        }

        //
        // utmdd($video_id, $markerArray);
    }
}
