<?php

namespace Mediatag\Commands\Test\HelperCmd\traits;

use FFMpeg\FFMpeg;
use Symfony\Component\Process\Process;
use Exception;
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
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\Option;
use UTMDbLib\Metatags\Artist;
use UTMDbLib\VideoInfo\VideoInfo as LibVinfo;
use Nette\Utils\FileSystem as NetteFile;
trait jsonExporter
{

    private $DbQuery = 'SELECT
    m.title as title ,m.artist as artist ,m.genre as genre ,m.studio as studio ,
    m.network as network ,m.keyword as keyword ,m.episode as episode ,m.scene as scene ,
    m.season as season ,m.movie as movie ,i.format,i.bit_rate,i.width,i.height,i.filesize,
    i.duration,i.chapters,v.filename,v.fullpath,v.rating,v.Library,v.subLibrary,v.id,v.video_key,
    v.thumbnail,v.preview,v.added,v.FavoriteVideo,v.playcount
    FROM
    mediatag_video_file v INNER JOIN mediatag_video_metadata m on v.video_key=m.video_key
     LEFT OUTER JOIN mediatag_video_info i on v.video_key=i.video_key
     WHERE
        v.Library = "Studios"  ';

    public function exportJson()
    {
          $db  = MysqliDb::getInstance();
        $res = $db->rawQuery($this->DbQuery);
        foreach ($res as $k => $v) {
            $videoId = $v['id'];
           if( $artist = self::getArtists($videoId)){
                // $res[$k]['artist'] = $artist;
                if( $artist != null && $artist != $res[$k]['artist']){
                    if($res[$k]['artist'] != null && $res[$k]['artist'] != ''){
                        $res[$k]['artist'] = $artist . ', ' . $res[$k]['artist'];
                    }else{
                        $res[$k]['artist'] = $artist;
                    }
                    // $v['artist'] = $artist . ', ' . $v['artist'];
                }
            }
            //$v['artist'] = self::getArtists($videoId);

//            $res[$k]['artist'] = date('Y-m-d H:i:s', strtotime($v['added']));
        }
        // $videoId = $res[4]['id'];
        // $artists = self::getArtists($videoId);
        foreach($res as $k => $v){
            $jsonId = $v['video_key'];
            $jsonFile = Path::join('/home/bjorn/scripts/Mediatag/var/test', $jsonId . '.info.json');
            // utmdd($v);
            file_put_contents($jsonFile, json_encode($v, JSON_UNESCAPED_SLASHES));// | JSON_PRETTY_PRINT));
        }
         utmdd($res[4]);

    }

    public static function getArtists($video_id = 0, $queryOnly = false)
    {
          $db  = MysqliDb::getInstance();

        $select     = ' ph.star_name ';
        if ($queryOnly === true) {
            $select   = "GROUP_CONCAT(ph.star_name  SEPARATOR ', ') AS name ";
            $video_id = 'v.id';
        }

        $query      = 'SELECT ' . $select . ' FROM `mediatag_artist_ph` as ph left OUTER join `mediatag_artist_map` as m on ph.id = m.artist_id WHERE m.video_id = ' . $video_id;

        if ($queryOnly === true) {
            return '(' . $query . ') as artist ';
        }
        $artistList = [];
        $artists    = '';
        $results    = $db->rawQuery($query);
        if (\count($results) > 0) {
            foreach ($results as $k => $name) {
                $artistList[] = $name['star_name'];
            }

            $artists = implode(',', $artistList);
        }

        return $artists;
    }
}
