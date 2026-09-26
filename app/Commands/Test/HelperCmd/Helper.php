<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test\HelperCmd;

use Mediatag\Bundle\Dialog\Options\Common;
use Mediatag\Bundle\Dialog\Widgets\Buildlist;
use Mediatag\Bundle\Dialog\Widgets\Gauge;
use Mediatag\Bundle\Dialog\Widgets\Menu;
use Mediatag\Bundle\WhipTail\Controller as WhipTail;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\Traits\ScriptWriterHelper;
use Mediatag\Modules\Metatags\MetaTagInfo;
use Mediatag\Modules\TagBuilder\Json\Reader;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\TagBuilder\VideoPattern;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder as NetteFinder;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTMDbLib\Metatags\Artist;
use UTMDbLib\VideoInfo\VideoInfo as LibVinfo;

use function count;
use function dirname;

use const __LIBRARY__;
use const __PLEX_DOWNLOAD__;
use const __PLEX_DOWNLOADED__;
use const DIRECTORY_SEPARATOR;
use const FILTER_VALIDATE_URL;
use const PHP_URL_PATH;

trait Helper
{
    use MediaFFmpeg;
    use ScriptWriterHelper;

    public function sortFiles()
    {

$fileArray = [];
$dups = [];
        $finder      = new Finder();

        $baseDir = __CURRENT_DIRECTORY__;
        $dirParent =dirname($baseDir);
        $dirPrefix = Path::makeRelative($baseDir,dirname($baseDir));

          Mediatag::$Console->writeln($baseDir);
            Mediatag::$Console->writeln($dirParent);
            Mediatag::$Console->writeln($dirPrefix);

        $filelist = '/media/bjorns-pc/Pornhub/filelist.txt';
        


        $dirs        = $finder->files()->in($baseDir)->name('*.mp4');
        Mediatag::$Console->writeln( $dirs->count() . " files found");
        foreach ($dirs as $dir) {
            // $key = basename( ".info.json");
            $video_key = MediaFile::getVideoKey($dir->getRealPath(), 'Pornhub');
            $fileArray[$video_key][] = $dir->getRealPath();
            

        }

      
        foreach ($fileArray as $key => $vids) {
            if (\count($vids) > 1) {
                $dups[] = $vids;
            }
        }
  Mediatag::$Console->writeln( count($dups) . " duplicate files found");

        foreach ($dups as $i => $files) {
             $DupefilePath = '';
             $origFilePath =  Path::makeRelative(dirname($files[0]),$baseDir);
            //  utmdd($origFilePath);
            $dupeFile = $files[1];
            $dupeFile= Path::makeRelative($dupeFile, $baseDir);
            $filePath = FileSystem::joinPaths($baseDir, $dupeFile);
            if (file_exists($filePath)) {
                 Mediatag::$Console->writeln( "Renaming " . basename($dupeFile) ." ");
                $DupefilePath = FileSystem::joinPaths($dirParent,'..', 'dupes', $dirPrefix, $origFilePath, basename($dupeFile));
                          Mediatag::$Console->writeln($DupefilePath);

                // utmdd($filePath,$DupefilePath);
                FileSystem::createDir(\dirname($DupefilePath));
                FileSystem::rename($filePath, $DupefilePath);
                // utmdd($filePath,$DupefilePath);
            }

        }

        // utmdump($fileArray);

    }

    public function copyvideoid()
    {
        $db  = MysqliDb::getInstance();
        $q = 'SELECT f.id, f.video_key FROM `mediatag_video_file` f left join mediatag_video_metadata m on m.video_key = f.video_key where m.video_id is null limit 2500';
        $artistRes = $db->rawQuery($q);
        //   utmdd($artistRes);
        foreach ($artistRes as $v => $row) {
            $q2 = "UPDATE `mediatag_video_metadata` SET `video_id` = '" . $row['id'] . "' WHERE video_key like '" . $row['video_key'] . "'";
            Mediatag::$Console->writeln($q2);
            $db->rawQuery($q2);
            // utmdump($q2);

        }
    }

    private function artistOut($msg, $label = 'info')
    {
        if (\is_array($msg)) {
            foreach ($msg as $key => $str) {
                $this->artistOut($key . ' => ' . $str, $label);
            }
        } else {
            $string = '<' . $label . '>' . $msg . '</' . $label . '>';
            Mediatag::$Console->writeln($string);
        }
    }

    private function updateMetafield($video_key, $artist, $correntName)
    {

        $db  = MysqliDb::getInstance();
        $mquery = "SELECT artist FROM `mediatag_video_metadata` WHERE `video_key` LIKE '" . $video_key . "'";
        $artistRes = $db->rawQuery($mquery);
        $artist_array = explode(',', $artistRes[0]['artist']);
        $key = array_search($artist, $artist_array, true);
        $artist_array[$key] = $correntName;

        $artist_str = implode(',', $artist_array);

        $mquery = "UPDATE mediatag_video_metadata SET artist = '" . $artist_str . "' WHERE video_key LIKE '" . $video_key . "'";
        $this->artistOut($mquery, 'error');
        $db->rawQuery($mquery);
        // utmdump($mquery);


    }

    private function findArtist($name, $video_id)
    {
        $name = trim($name);
        $nameKey = strtolower(str_replace([' ', "'"], ['', ''], $name));
        $db  = MysqliDb::getInstance();

        $sql = "SELECT * FROM `mediatag_artist_ph` WHERE `nameKey` LIKE '" . $nameKey . "' ORDER BY `star_thumb` ASC";
        $res = $db->rawQuery($sql);

        if (\count($res) == 1) {
            $artist_id = $res[0]['id'];
            $equery = 'SELECT * FROM `mediatag_artist_map` where video_id = ' . $video_id . ' and artist_id = ' . $artist_id;
            $exists = $db->rawQuery($equery);
            // utmdump([$equery,$exists,count($exists)]);
            if (\count($exists) == 0) {
                $insertQ = 'INSERT INTO mediatag_artist_map (id,video_id, artist_id) VALUES (NULL, ' . $video_id . ',' . $artist_id . ')';
                $db->rawQuery($insertQ);
                $this->artistOut($insertQ . ' Artist => ' . $res[0]['star_name'], 'comment');
            }
            if ($name != $res[0]['star_name']) {
                utmdd($name, $res);
                $this->updateMetafield($this->video_key, $name, $res[0]['star_name']);
            }

            return $res[0]['star_name'];
        }
        // utmdump($res);

        return false;

    }

    public function getArtistList()
    {

        $db  = MysqliDb::getInstance();


        // $db->where("m.artist")


        // done dont run now.
        // $sql = 'SELECT f.id,m.video_key,map.artist_id, p.star_name FROM mediatag_video_metadata m  LEFT JOIN mediatag_video_file f on f.video_key = m.video_key LEFT join mediatag_artist_map map on map.video_id = f.id left join mediatag_artist_ph p on map.artist_id = p.id   WHERE m.artist IS NULL and map.artist_id is not NULL LIMIT 500;';
        // utmdd($sql);
        // $res = $db->rawQuery($sql);
        // foreach ($res as $i => $videoInfo) {
        //     $artistArray[$videoInfo['video_key']][] = $videoInfo['star_name'];

        // }
        // $z = 0;
        // foreach ($artistArray as $video_key => $artist_array) {
        //     $z++;
        //     $artists = implode(',', $artist_array);
        //     $q2 = "UPDATE mediatag_video_metadata SET artist = '" . $artists . "' WHERE video_key = '" . $video_key . "'";
        //     $resres2 = $db->query($q2);
        //     utmdump([$z => $q2]);
        // }



        $q     = 'SELECT f.id,m.video_key,m.artist FROM `mediatag_video_metadata` m left join mediatag_video_file f on f.video_key = m.video_key left join mediatag_artist_map map on map.video_id = f.id where m.artist is not NULL ORDER BY f.id ASC';
        $q     .= ' limit 100';
        $this->artistOut($q, 'question');
        // utmdd($q);
        $result = $db->query($q);
        foreach ($result as $i => $row) {
            $video_id = $row['id'];
            $this->video_key = $row['video_key'];
            $artist_key = $row['artist'];
            if (str_contains($artist_key, ',')) {
                $artists = explode(',', $artist_key);
                utmdump($artists);
                foreach ($artists as $n => $name) {
                    $sucess = $this->findArtist($name, $video_id);
                    if ($sucess === false) {
                        $this->artistOut([$name, $video_id, $this->video_key], 'question');
                        Mediatag::$Console->writeln('');

                        continue;
                    }
                    $this->artistOut($sucess, 'info');
                    // $this->updateMetafield($video_key, $name);
                }

                continue;
            }

            $sucess =  $this->findArtist($artist_key, $video_id);
            if ($sucess === false) {
                $this->artistOut([$artist_key, $video_id, $this->video_key], 'error');
                Mediatag::$Console->writeln('');

                continue;
            }
            // $this->updateMetafield($video_key, $artist_key);
        }

        // $q = 'SELECT  DISTINCT(video_id) FROM `mediatag_artist_map`';
        // $users = $db->rawQuery($q);
        // foreach ($users as $i => $info) {
        //     $video_id = $info['video_id'];
        //     $query = 'SELECT * FROM `mediatag_video_file` WHERE `id` = ' . $video_id;
        //     $res = $db->rawQueryOne($query);
        //     if ($res === null) {
        //         Mediatag::$Console->text(["removing map for video ID $video_id"]);
        //         $del_q = 'DELETE FROM mediatag_artist_map WHERE `mediatag_artist_map`.`video_id` = ' . $video_id;
        //         $dres = $db->rawQuery($del_q);
        //     }




        // }



        // utmdump($users);

    }

    public function regextest()
    {

        // $pat = '<ALL>? SEP_D SCENE SEP_U SEASON SEP_U <ALL> FILE_RES_LONG';
        $pat = '[glamkore|pretty_and_raw|rammed|trickery]? SEP_D? <ALL> SEP_D SCENE SEP_DOT FILE_RES_LONG ';

        // 'pattern'             => '/(glamkore|pretty_and_raw|rammed|trickery)\_([a-zA-Z_]{1,})[0-9]?\_scene.*[0-9]{1,4}.*\.mp4/i',

        $str =          'DirtyLittleCheerleaderStories-Scene1_s01_ChadWhite_LilyLarimar_1080p_h264.mp4';
        $str2 =          'DirtyLittleCheerleaderStories-Scene1_s01_ChadWhite_LilyLarimar_1080p.mp4';
        $str = 'hooked-up-scene-3.1080p.mp4';

        $patt =  VideoPattern::pattern($pat);
        Mediatag::$Console->note($patt);


        preg_match($patt, $str, $out);
        Mediatag::$Console->text($out);

        preg_match($patt, $str2, $out);
        Mediatag::$Console->text($out);

    }

    public function guiTest()
    {
        $filelist_array = $this->VideoList['file'];

        $common = new \Mediatag\Bundle\Dialog\Options\Common(
            ['backtitle', 'Testing Dialog...'],
        );

        foreach ($filelist_array as $key => $fileInfo) {
            $items[] = new \Mediatag\Bundle\Dialog\Options\Item($key, $fileInfo['video_name']);
        }

        $box = new \Mediatag\Bundle\Dialog\Widgets\Buildlist('Change items:', 0, ...$items);
        $dialog = new \Mediatag\Bundle\Dialog\Dialog($common, $box);
        $dialog->run();


        echo \PHP_EOL, 'Output is: ', $dialog->output(), \PHP_EOL, 'Exit code: ', $dialog->exit_code(), \PHP_EOL;



    }

    public function moveJsonCache()
    {
        $file_string = '';

        $finder      = new Finder();
        $dirs        = $finder->files()->in(\__PLEX_DOWNLOAD__)->name('*.json'); // ->depth(0);
        foreach ($dirs as $dir) {
            // $key = basename( ".info.json");
            $video_key = MediaFile::getVideoKey($dir->getRealPath());
            $newFile   = MediaFile::getjsonFilename(__JSON_CACHE_DIR__, $video_key);
            //  $file_string .= 'https://www.pornhub.com/view_video.php?viewkey=' . $key . PHP_EOL;


            if (file_exists($newFile)) {
                Mediatag::$Console->writeln('<info> deleting ' . $dir->getRealPath() . ' </>');
                unlink($dir->getRealPath());

                continue;
            }
            FileSystem::rename($dir->getRealPath(), $newFile, false);
            Mediatag::$Console->writeln('<info>' . $newFile . ' </>');
            // exit;
        }

        // MediaFilesystem::writeFile(Process::JSONPLAYLIST, $file_string);

    }

    public function fixPhVideos()
    {
        $finder = new Finder();
        $dirs   = $finder->directories()->in('/media/Videos/Plex/XXX/Pornhub/Studios/Adult Mobile');
        foreach ($dirs as $dir) {
            Mediatag::$Console->writeln('<info>' . $dir->getRealPath() . '</>');


        }
    }

    public function RenamePrivate()
    {
        $filelist_array = $this->VideoList['file'];
        foreach ($filelist_array as $key => $fileInfo) {
            $existing_json_file = null;
            if (preg_match('/\-([a-zA-Z0-9]{0,6}_[a-zA-Z0-9]{3}_[0-9]{0,4}\.mp4)/', $fileInfo['video_name'], $output_array)) {
                // find old jsonFile
                // rename old file to new file

                $video_file  = $fileInfo['video_file'];
                $new_file    = $fileInfo['video_path'] . \DIRECTORY_SEPARATOR . $output_array[1];

                Mediatag::$Console->writeln('<info>renaming</>');
                Mediatag::$Console->writeln('<comment>' . $fileInfo['video_name'] . ' to </>');
                Mediatag::$Console->writeln('<comment>' . $new_file . ' </>');

                FileSystem::rename($video_file, $new_file, false);
                $videoFile   = new MediaFile($new_file);
                $newvideokey = $videoFile->videokey();

                $json_file   = __STUDIO_JSON_CACHE_DIR__ . '/' . $fileInfo['video_key'] . '.info.json';
                $newJsonFile = __STUDIO_JSON_CACHE_DIR__ . '/' . $newvideokey . '.info.json';
                if (file_exists($json_file)) {
                    FileSystem::rename($json_file, $newJsonFile);
                }

                // utmdump([$json_file, $newJsonFile, $video_file, $new_file]);
            }
            Mediatag::$Console->writeln('<info>' . $fileInfo['video_name'] . '</>');
        }

        // preg_match('/\-([a-zA-Z0-9]{0,6}_[a-zA-Z0-9]{3}_[0-9]{0,4}\.mp4)/', $input_line, $output_array);
    }

    public function getJsonFilelist()
    {
        $conn         = new StorageDB();

        $file_array   = $conn->getDbFileList();

        $filearray    = [];
        $jsonFileList = [];

        // utmdump($this->file_array);
        foreach ($file_array as $json_key => $file) {
            $backupFile = '';
            if (str_starts_with($json_key, 'x')) {
                $json_file = __STUDIO_JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
            } else {
                $json_file = __JSON_CACHE_DIR__ . '/' . $json_key . '.info.json';
            }

            if (file_exists($json_file)) {
                $json_file            = Reader::checkJsonForUpdate($json_file, $json_key);

                $filearray[$json_key] = ['file' => $file, 'json' => $json_file];
                $jsonFileList[]       = $file;
            }
        }

        $this->NewFilesCommandScript(
            $jsonFileList,
            [
                'filename' => 'UpdateNewFiles.sh',
                'command'  => 'update',
                'options'  => ['update', '-f'],
            ],
        );
    }

    public function subtitlepath($file)
    {
        $fileInfo     = pathinfo($file);
        $directory    = $fileInfo['dirname'];
        $filename     = $fileInfo['filename'];
        $extension    = $fileInfo['extension'];
        // 2 "/media/Videos/Plex/XXX/Studios/Adult Time/Watch You Cheat/MFF/Subtitles"
        $subtitlePath = str_replace('Subtitles/', '', $directory) . \DIRECTORY_SEPARATOR . 'Subtitles' . \DIRECTORY_SEPARATOR;
        FileSystem::createDir($subtitlePath);

        return $subtitlePath . $filename . '.' . $extension;
    }

    public function moveSubtitles()
    {
        $file_array = Mediatag::$finder->Search(\__PLEX_HOME__ . \DIRECTORY_SEPARATOR . 'Subtitles', '*.srt*', exit: false);
        foreach ($file_array as $file) {
            $newFile = $this->subtitlepath($file);

            FileSystem::rename($file, $newFile);
            // utmdd($file, $newFile);
        }
    }

    public function listMarkers()
    {
        $db  = MysqliDb::getInstance();

        $map = [
            // 'plexweb_video_markers' => 'video_id',
            'plexweb_playlist_videos' => 'playlist_video_id',
            'plexweb_favorite_videos' => 'video_id',
            'mediatag_artist_map'     => 'video_id',
        ];

        foreach ($map as $table => $column) {
            $q     = 'SELECT DISTINCT ' . $column . ' FROM ' . $table . ' order by ' . $column . ' ';
            $users = $db->rawQuery($q);
            // utmdd($users, $db->getLastQuery());
            foreach ($users as $user) {
                $db->where('id', $user[$column]);
                $res = $db->getOne('mediatag_video_file');

                if ($res === null) {
                    $db->where($column, $user[$column]);
                    $db->delete($table);

                    // } else {
                    // utmdump($db->getLastQuery());
                }
            }
        }
    }

    public $videoFile;

    private function getMarkerThumbPath($file)
    {
        $img_web_path = (new MediaFilesystem())->makePathRelative($file, __PLEX_HOME__);
        // utmdump($img_web_path);
    }

    public function hello()
    {
        Mediatag::$Console->writeln('<info>Hellow people</>');
    }

    public function testMove()
    {
        $keys = [
            '68f80a6b8693f',
            'ph598a38f3ec724',
            'ph57224bb888c7b',
            'ph62c72845145ab',
            '66b6763928848',
            '667b0676eca47',
            '66422cd3897da',
            '65de3833278d6',
            '6678c1418b11f',
            '6781418a09c7c',
            '685b203d51d96',
            '67546761d5896',
            '68752bd20599a',
            '68bb42fdace1f',
        ];

        foreach ($keys as $key) {
            Mediatag::$Console->writeln('searching for key ' . $key);
            $file_array = Mediatag::$finder->Search(\__PLEX_DOWNLOAD__, '*' . $key . '*', exit: false);
            if (\count($file_array) > 0) {
                foreach ($file_array as $file) {
                    if (str_ends_with($file, '.mp4')) {
                        $currentPath  = \dirname($file);
                        $filename     = \DIRECTORY_SEPARATOR . basename($file, '.mp4');

                        $jsonFile     = $filename . '.info.json';
                        $videoFile    = $filename . '.mp4';

                        $newPath      = str_replace(\__PLEX_DOWNLOAD__, \__PLEX_DOWNLOADED__, $currentPath);
                        FileSystem::createDir($newPath);

                        $newVideoFile = $newPath . $videoFile;
                        $newJsonFile  = $newPath . $jsonFile;

                        FileSystem::rename($currentPath . $videoFile, $newVideoFile);
                        FileSystem::rename($currentPath . $jsonFile, $newJsonFile);
                        Mediatag::$Console->writeln('Moved Completed file <file>' . $videoFile . ' to downloaded </file>');
                    }
                }
            }
        }
    }

    public function importThumb()
    {
        $db        = MysqliDb::getInstance();

        $this->max = 2000;
        $db->where('gender', 'female');
        $db->where('star_thumb', '%media%', 'Not like');
        $res       = $db->map('star_name')->get('mediatag_artist_ph', $this->max);

        foreach ($res as $i => $row) {
            $nameKey    = strtolower(str_replace(' ', '_', $row['star_name']));
            $db->where('star_name', $nameKey);
            //  $db->where('star_thumb', '%media%', 'like');
            $res2       = $db->getone('mediatag_artist_ph1');
            $thumbnail  = $res2['star_thumb'];
            // utmdd($res2, $db->getLastQuery());

            $newnameKey = strtolower(str_replace('_', '', $res2['star_name']));
            if (str_contains($thumbnail, 'media')) {
                $query = "UPDATE ignore `mediatag_artist_ph` SET `star_thumb` = '" . $thumbnail . "' WHERE `mediatag_artist_ph`.`nameKey` = '" . $newnameKey . "'  and `mediatag_artist_ph`.`star_thumb`  not like '%media%'";
                // utmdd($query);
                $db->rawQuery($query);
                Mediatag::$Console->writeln('<info>' . $this->max . '</> Added ' . $row['star_name']);
                // utmdump($query);
            } else {
                $thumbnail = $this->saveArtistThumbnail($newnameKey, $thumbnail);
            }
            $this->max--;
            // utmdd($query);
        }
    }

    public function splitMethod()
    {
        $filename = __DIR__ . '/output.csv';

        $split    = 3000;
        // utmdump($filename);
        MediaFile::splitFile($filename, __DIR__, $split, 'batch_', '.csv');
    }

    public function searchPh()
    {
        // $client = Factory::create(['base_uri' => 'https://www.pornhub.com/webmasters/']);
        // $client->disableHttpErrorExceptions();
        // // $client->disableResponseWrapper();
        // $response = $client->stars()->getDetailed();
        // $search   = $response->toArray();

        include __DIR__ . '/output.php';
        $filename = __DIR__ . '/output/%N/output_%D.php';
        $chunks   = array_chunk($search['stars'], 500);
        $data     = [];
        $phpFiles = '';
        foreach ($chunks as $i => $pcs) {
            $data    = [];
            foreach ($pcs as $x => $star) {
                // $starAr = $star['star'];
                if ($star['star']['videos_count_all'] == '0') {
                    $data['NV'][] = $star['star'];

                    continue;
                }

                if ($star['star']['gender'] == 'male' || $star['star']['gender'] == 'female') {
                    $data['Stars'][] = $star['star'];
                } elseif ($star['star']['gender'] == 'unknown') {
                    $data['Unknown'][] = $star['star'];
                } else {
                    $data['NG'][] = $star['star'];
                }
            }
            $phpFile = str_replace('%D', $i, $filename);
            foreach ($data as $key => $array) {
                // $count[$key] = count($array);
                $arrayCode = "<?php\nreturn " . var_export($array, true) . ";\n";
                $phpFiles  = str_replace('%N', $key, $phpFile);
                file_put_contents($phpFiles, $arrayCode);
            }
            // $arrayCode = "<?php\nreturn " . var_export($data, true) . ";\n";
            // $phpFile = str_replace('%D', $i, $filename);
            // file_put_contents($phpFile, $arrayCode );
        }
    }

    public function importActors()
    {
        $db       = MysqliDb::getInstance();

        $actorDir = __DIR__ . '/output/Stars';
        foreach (NetteFinder::findFiles('*.php')->in($actorDir) as $name => $file) {
            Mediatag::$Console->writeln('Including ' . $file);
            $array        = require_once $file;
            $arrayChunk   = array_chunk($array, 50);
            foreach ($arrayChunk as $i => $starChunks) {
                $insertData = [];
                foreach ($starChunks as $x => $star) {
                    // $star['star_name'] = \strtolower($star['star_name']);
                    $star['star_name'] = trim($star['star_name']);
                    $star['nameKey']   = strtolower(str_replace(' ', '', $star['star_name']));
                    // $star['star_name'] = \str_replace(' ', '_', $star['star_name']);
                    // $star['nameKey'] = \str_replace(' ', '', $star['star_name']);
                    $insertData[]      = $star;
                }

                $id         = $db->setQueryOption('IGNORE')->insertMulti('mediatag_artist_ph', $insertData);
                // utmdd($id,$db->getLastQuery());
                // Mediatag::$Console->writeln($id . ' Added ' . $star['star_name']);

                // $exists = $db->where('star_name', $star['star_name'])->getOne('mediatag_artist_ph');
                // if (is_null($exists)) {
                //
                //
                // } else {
                //     Mediatag::$Console->writeln('<info>Skipping ' . $star['star_name'] . '</>');
                // }
            }

            $finishedFile = str_replace('/output/', '/output/finished/', $file);

            FileSystem::rename($file, $finishedFile, overwrite: true);
            // unset($array);
            // utmdd($finishedFile);
        }

        return true;
    }

    public function Actor()
    {
        $filelist_array = $this->VideoList['file'];
        foreach ($filelist_array as $key => $row) {
            $videoId = VideoInfo::GetVideoIdByKey($key);
            // utmdump($videoId);
        }
        MetaTagInfo::getTagIDbyValue('artist', 'Mick Blue');
    }
    // $arrayCodeNG = "<?php\nreturn " . var_export($NonGenderdata, true) . ";\n";
    // $arrayCodeNG = "<?php\nreturn " . var_export($NonGenderdata, true) . ";\n";

    // $phpFile   = str_replace('%D', 'G', $filename);
    // $phpFileNG = str_replace('%D', 'NG', $filename);

    // file_put_contents($phpFile, $arrayCode);

    // file_put_contents($phpFileNG, $arrayCodeNG);
    //  $file     = fopen($filename, 'w');

    // foreach ($search as $v => $stars) {

    // fclose($file);

    // utmdd($id, $exists);

    // Mediatag::$Console->writeln($video);

    public function getVideoInfo()
    {
        new Artist(__MYSQL_ARTIST_PH__, __MYSQL_ARTIST_MAP__);
        $vInfo          = new LibVinfo(__MYSQL_VIDEO_FILE__);
        $vInfo->setLibrary(\__LIBRARY__);
        // LibVinfo
        $filelist_array = $this->VideoList['file'];
        // Mediatag::$Display->LineBreaks = true;
        // Mediatag::$Display->DisplayTable($filelist_array);
        // Mediatag::$Console->writeln('');
        $tag            = 'artist';
        foreach ($filelist_array as $key => $row) {
            $info     = new TagReader();
            // $info->taglist = [$tag];
            $info->loadVideo($row);
            $data     = $info->getMetaValues();
            $tagValue = $data[$tag];

            $videoId  = $vInfo->getvideoId($key);

            if ($tag == 'artist') {
                $r = Artist::updateArtistMap($videoId, $tagValue);
                // MetaTagInfo::updateArtistMap($videoId, $tag, $tagValue);
            }
        }
        // utmdump($this->VideoList);
        // foreach ($this->VideoList['file'] as $videoInfo) {
        //     $this->displayTable->displayTable($videoInfo);
        // }
    }

    private function saveArtistThumbnail($artist, $thumbnail)
    {
        $db = MysqliDb::getInstance();

        //         $imageUrl = "https://example.com/sample.jpg";
        // $saveDir  = __DIR__ . "/images"; // Ensure this folder exists and is writable

        if (str_contains($thumbnail, 'phncdn')) {
            Mediatag::$Console->writeln('<info>' . $this->max . '</> Changin PH Thumbnail for ' . $artist);
            $img_file_path = '/home/bjorn/www/plex_web/html/images/thumbnails';

            $NewThumbnail  = $this->saveImageFromUrl($thumbnail, $img_file_path);
            // utmdd($NewThumbnail);
            if ($NewThumbnail !== false) {
                $data      = ['star_thumb' => $NewThumbnail];
                $db->where('nameKey', $artist);
                $db->update('mediatag_artist_ph', $data);
                $thumbnail = $NewThumbnail;
            }
        }

        return $thumbnail;
    }

    /**
     * Save an image from a given URL to a local folder.
     *
     * @param  string  $imageUrl  The full URL of the image
     * @param  string  $saveDir  The local folder path (must be writable)
     * @param  string|null  $fileName  Optional custom file name (with extension)
     * @return string|false Path to saved file on success, false on failure
     */
    public function saveImageFromUrl($imageUrl, $saveDir, $fileName = null)
    {
        // Validate URL
        if (! filter_var($imageUrl, \FILTER_VALIDATE_URL)) {
            echo "Invalid URL.\n";

            return false;
        }

        // Ensure save directory exists and is writable
        if (! is_dir($saveDir) || ! is_writable($saveDir)) {
            echo "Save directory does not exist or is not writable.\n";

            return false;
        }

        // Get image content
        $imageData    = @file_get_contents($imageUrl);
        if ($imageData === false) {
            echo "Failed to fetch image from URL.\n";

            return false;
        }

        // Determine file name
        if ($fileName === null) {
            $urlPath   = parse_url($imageUrl, \PHP_URL_PATH);

            $fileName  = basename($urlPath);
            $fileName  = Strings::after($fileName, ')', 2);

            $dir       = [];
            $fileId    = Strings::after(basename($fileName, '.jpg'), '_');
            if ($fileId !== null) {
                $dir = str_split($fileId, 2);
                array_pop($dir);
            }
            $imagePath = \DIRECTORY_SEPARATOR . implode(\DIRECTORY_SEPARATOR, $dir);
            // for ($i = 0; $i < $len; $i++) {
            //     $dir[] = $fileId[$i] . $fileId[$i++];
            // }

            // utmdump([$imageUrl, $urlPath, $fileName, $fileId[0]]);
            if (empty($fileName)) {
                $fileName = uniqid('img_', true) . '.jpg'; // fallback
            }
        }

        // Full save path
        $imagePath    = rtrim($imagePath, \DIRECTORY_SEPARATOR);
        $savePath     = rtrim($saveDir, \DIRECTORY_SEPARATOR) . $imagePath;

        FileSystem::createDir($savePath);
        $saveFile     = $savePath . \DIRECTORY_SEPARATOR . $fileName;
        $img_web_path = 'http://media.lan/plex/images/thumbnails' . $imagePath . \DIRECTORY_SEPARATOR . $fileName;

        // utmdd($saveFile, $img_web_path);
        // Save file
        if (! file_exists($saveFile)) {
            if (file_put_contents($saveFile, $imageData) === false) {
                echo "Failed to save image to folder.\n";

                return false;
            }
        }

        return $img_web_path;
    }
}
