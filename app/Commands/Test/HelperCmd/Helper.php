<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Test\HelperCmd;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

use FFMpeg\FFMpeg;
use Mediatag\Bundle\Grephp\Grephp;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Metatags\MetaTagInfo;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\VideoInfo\VideoInfo;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder as NetteFinder;
use Paramako\Pornhub\Factory;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;

use function dirname;

trait Helper
{
    public $videoFile;

    public function splitMethod()
    {
        $filename = __DIR__ . '/output.csv';

        $split = 3000;
        utmdump($filename);
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
                    $star['star_name'] = \strtolower($star['star_name']);
                    $star['star_name'] = trim($star['star_name']);
                    $star['star_name'] = \str_replace(' ', '_', $star['star_name']);
                    $insertData[]      = $star;
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
        $filelist_array                = $this->VideoList['file'];
        Mediatag::$Display->LineBreaks = true;
        Mediatag::$Display->DisplayTable($filelist_array);
        Mediatag::$Console->writeln('');

        $tag = 'artist';
        foreach ($filelist_array as $key => $row) {
            $info          = new TagReader;
            $info->taglist = [$tag];
            $info->loadVideo($row);
            $data     = $info->getMetaValues();
            $tagValue = $data[$tag];

            $videoId = VideoInfo::GetVideoIdByKey($key);
            if ($tag == 'genre') {
                MetaTagInfo::updateGenreMap($videoId, $tag, $tagValue);
            }
            if ($tag == 'artist') {
                MetaTagInfo::updateArtistMap($videoId, $tag, $tagValue);
            }
        }
        // utmdump($this->VideoList);
        // foreach ($this->VideoList['file'] as $videoInfo) {
        //     $this->displayTable->displayTable($videoInfo);
        // }
    }
}
