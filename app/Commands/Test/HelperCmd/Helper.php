<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test\HelperCmd;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Mediatag\Bundle\Grephp\Grephp;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\Metatags\MetaTagInfo;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\VideoData\VideoData;
use Mediatag\Modules\VideoInfo\Section\Markers;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Mediatag\Traits\MediaFFmpeg;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder as NetteFinder;
use Nette\Utils\Strings;
use Paramako\Pornhub\Factory;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTMDbLib\Metatags\Artist;
use UTMDbLib\VideoInfo\VideoInfo as LibVinfo;

use function dirname;

trait Helper
{
    use MediaFFmpeg;

    public function subtitlepath($file)
    {
        $fileInfo  = pathinfo($file);
        $directory = $fileInfo['dirname'];
        $filename  = $fileInfo['filename'];
        $extension  = $fileInfo['extension'];
        // 2 "/media/Videos/Plex/XXX/Studios/Adult Time/Watch You Cheat/MFF/Subtitles"
        $directory    = str_replace('Subtitles', '', $directory);
        $subtitlePath = str_replace('Plex/XXX', 'Plex/XXX' . DIRECTORY_SEPARATOR . 'Subtitles', $directory);
        FileSystem::createDir($subtitlePath);

        return $subtitlePath  . $filename .'.'.  $extension;
    }

    public function moveSubtitles()
    {
        $file_array = Mediatag::$finder->Search(\__PLEX_HOME__ . DIRECTORY_SEPARATOR . 'Studios', '*.srt*', exit: false);
        foreach ($file_array as $file) {
            $newFile = $this->subtitlepath($file);
                        // utmdd($file, $newFile);

                                    FileSystem::rename($file, $newFile);

        }
    }

    public function listMarkers()
    {
        $db = MysqliDb::getInstance();

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

                if (is_null($res)) {
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
        $img_web_path = (new MediaFilesystem)->makePathRelative($file, __PLEX_HOME__);
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
            if (count($file_array) > 0) {
                foreach ($file_array as $file) {
                    if (str_ends_with($file, '.mp4')) {
                        $currentPath = dirname($file);
                        $filename    = DIRECTORY_SEPARATOR . basename($file, '.mp4');

                        $jsonFile  = $filename . '.info.json';
                        $videoFile = $filename . '.mp4';

                        $newPath = \str_replace(\__PLEX_DOWNLOAD__, \__PLEX_DOWNLOADED__, $currentPath);
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
        $db = MysqliDb::getInstance();

        $this->max = 2000;
        $db->where('gender', 'female');
        $db->where('star_thumb', '%media%', 'Not like');
        $res = $db->map('star_name')->get('mediatag_artist_ph', $this->max);

        foreach ($res as $i => $row) {
            $nameKey = \strtolower(str_replace(' ', '_', $row['star_name']));
            $db->where('star_name', $nameKey);
            //  $db->where('star_thumb', '%media%', 'like');
            $res2      = $db->getone('mediatag_artist_ph1');
            $thumbnail = $res2['star_thumb'];
            // utmdd($res2, $db->getLastQuery());

            $newnameKey = \strtolower(str_replace('_', '', $res2['star_name']));
            if (\str_contains($thumbnail, 'media')) {
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

        $split = 3000;
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
            $data = [];
            foreach ($pcs as $x => $star) {
                //$starAr = $star['star'];
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
        $db = MysqliDb::getInstance();

        $actorDir = __DIR__ . '/output/Stars';
        foreach (NetteFinder::findFiles('*.php')->in($actorDir) as $name => $file) {
            Mediatag::$Console->writeln('Including ' . $file);
            $array      = require_once $file;
            $arrayChunk = \array_chunk($array, 50);
            foreach ($arrayChunk as $i => $starChunks) {
                $insertData = [];
                foreach ($starChunks as $x => $star) {
                    // $star['star_name'] = \strtolower($star['star_name']);
                    $star['star_name'] = trim($star['star_name']);
                    $star['nameKey']   = strtolower(str_replace(' ', '', $star['star_name']));
                    // $star['star_name'] = \str_replace(' ', '_', $star['star_name']);
                    // $star['nameKey'] = \str_replace(' ', '', $star['star_name']);
                    $insertData[] = $star;
                }

                $id = $db->setQueryOption('IGNORE')->insertMulti('mediatag_artist_ph', $insertData);
                // utmdd($id,$db->getLastQuery());
                //Mediatag::$Console->writeln($id . ' Added ' . $star['star_name']);

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
        $vInfo = new LibVinfo(__MYSQL_VIDEO_FILE__);
        $vInfo->setLibrary(\__LIBRARY__);
        //LibVinfo
        $filelist_array = $this->VideoList['file'];
        // Mediatag::$Display->LineBreaks = true;
        // Mediatag::$Display->DisplayTable($filelist_array);
        // Mediatag::$Console->writeln('');
        $tag = 'artist';
        foreach ($filelist_array as $key => $row) {
            $info = new TagReader;
            // $info->taglist = [$tag];
            $info->loadVideo($row);
            $data     = $info->getMetaValues();
            $tagValue = $data[$tag];

            $videoId = $vInfo->getvideoId($key);

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

            $NewThumbnail = $this->saveImageFromUrl($thumbnail, $img_file_path);
            // utmdd($NewThumbnail);
            if ($NewThumbnail !== false) {
                $data = ['star_thumb' => $NewThumbnail];
                $db->where('nameKey', $artist);
                $db->update('mediatag_artist_ph', $data);
                $thumbnail = $NewThumbnail;
                //
            }
        }

        return $thumbnail;
    }

    /**
     * Save an image from a given URL to a local folder
     *
     * @param  string  $imageUrl  The full URL of the image
     * @param  string  $saveDir  The local folder path (must be writable)
     * @param  string|null  $fileName  Optional custom file name (with extension)
     * @return string|false Path to saved file on success, false on failure
     */
    public function saveImageFromUrl($imageUrl, $saveDir, $fileName = null)
    {
        // Validate URL
        if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            echo "Invalid URL.\n";

            return false;
        }

        // Ensure save directory exists and is writable
        if (! is_dir($saveDir) || ! is_writable($saveDir)) {
            echo "Save directory does not exist or is not writable.\n";

            return false;
        }

        // Get image content
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false) {
            echo "Failed to fetch image from URL.\n";

            return false;
        }

        // Determine file name
        if ($fileName === null) {
            $urlPath = parse_url($imageUrl, PHP_URL_PATH);

            $fileName = basename($urlPath);
            $fileName = Strings::after($fileName, ')', 2);

            $dir    = [];
            $fileId = Strings::after(basename($fileName, '.jpg'), '_');
            if (! is_null($fileId)) {
                $dir = str_split($fileId, 2);
                \array_pop($dir);
            }
            $imagePath = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dir);
            // for ($i = 0; $i < $len; $i++) {
            //     $dir[] = $fileId[$i] . $fileId[$i++];
            // }

            // utmdump([$imageUrl, $urlPath, $fileName, $fileId[0]]);
            if (empty($fileName)) {
                $fileName = uniqid('img_', true) . '.jpg'; // fallback
            }
        }

        // Full save path
        $imagePath = rtrim($imagePath, DIRECTORY_SEPARATOR);
        $savePath  = rtrim($saveDir, DIRECTORY_SEPARATOR) . $imagePath;

        FileSystem::createDir($savePath);
        $saveFile     = $savePath . DIRECTORY_SEPARATOR . $fileName;
        $img_web_path = 'http://media.lan/plex/images/thumbnails' . $imagePath . DIRECTORY_SEPARATOR . $fileName;

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
