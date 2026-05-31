<?php

namespace Mediatag\Commands\Update\Commands\Artist;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Modules\VideoInfo\VideoInfo;
use UTM\Bundle\mysql\MysqliDb;
use UTMDbLib\Metatags\Artist;

trait ArtistHelper
{
    public function updateAristMap()
    {
        $fileArray = $this->VideoList['file'];

        foreach ($fileArray as $key => $videoInfo) {
            $video_id = $tagObj = new TagReader;
            $tagObj->loadVideo($videoInfo);

            $tagBuilder     = new TagBuilder($key, $tagObj);
            $videoArray     = $tagBuilder->getTags($videoInfo);
            $video_id       = (new VideoInfo)->getvideoId($key);
            $this->video_id = $video_id;
            $artistString   = $videoArray['currentTags']['artist'];
            $artistArray    = explode(',', $artistString);
            $artistArray    = $this->mapArtisttoId($artistArray);
            $r              = $this->addArtistIdToMap($video_id, $artistArray);
        }
    }

    private function addArtistIdToMap($video_id, $artistArray)
    {
        $db = MysqliDb::getInstance();

        $db->where('video_id', $video_id);
        $db->where('artist_id', $video_id);
        $db->delete(__MYSQL_ARTIST_MAP__);

        foreach ($artistArray as $artistRow) {
            $artistid = $artistRow['id'];
            $db->where('video_id', $video_id);
            $db->where('artist_id', $artistid);
            $res = $db->delete(__MYSQL_ARTIST_MAP__);

                $data = ['video_id' => $video_id,  'artist_id' => $artistid];
                $id[] = $db->insert(__MYSQL_ARTIST_MAP__, $data);

        }

        return $id;
    }

    private function mapArtisttoId($array)
    {
        $db     = MysqliDb::getInstance();
        $result = [];
        foreach ($array as $artist) {
            $v = \strtolower(str_replace(' ', '', $artist));
             $v = \strtolower(str_replace('.', '', $v));

            if($artist == "") {
                continue;
            }

            $db->where('nameKey', $v, 'like');
            $oneRes = $db->getone(\__MYSQL_ARTIST_PH__);

            if ($oneRes === null) {
                $db->where('nameKey', $v . '%', 'like');
                $res = $db->get(\__MYSQL_ARTIST_PH__);
                if (count($res) == 1) {

                   Mediatag::$Console->writeln("match found for " . $this->video_id ."  ".  $res[0]['star_name']);

                    // utmdump(['Found possible Match for ' . $this->video_id => [$res[0]['star_name'], $artist]]);
                    // if ($res[0]['gender'] != 'male') {
                    $result[] = ['id' => $res[0]['id'], 'Artist' => $res[0]['star_name']];
                    // $result = $res[0];
                    // }
                } elseif (count($res) == 0) {
                    Mediatag::$Console->writeln('No match found for ' . $this->video_id ." '". $artist. "'");
                } else {
                    $res = array_slice($res, 0, 5);
                    foreach ($res as $row) {
                        $matches[] = [$row['star_name'], $artist];
                        // if ($row['gender'] != 'male') {
                        $result[] = ['id' => $row['id'], 'Artist' => $row['star_name']];
                        // utmdump([$artist, $row['star_name']]);
                        // }
                    }

 Mediatag::$Console->writeln("ound multiple Matches for  " . $this->video_id ."  ".  $$artist );
                    // utmdump(['Found multiple Matches for ' . $artist => $matches]);
                }
            } else {
                // utmdump(['Found Match for ' . $this->video_id => $oneRes['star_name']]);
                // if ($oneRes['gender'] != 'male') {
                Mediatag::$Console->writeln("match found for " . $this->video_id ."  ".  $oneRes['star_name']);

                $result[] = ['id' => $oneRes['id'], 'Artist' => $oneRes['star_name']];
                // }
            }
        }

        return $result;
    }
}
