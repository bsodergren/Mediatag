<?php

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use UTM\Bundle\mysql\MysqliDb;
use UTM\Utilities\DynamicProperty;

class MetaTagInfo extends Mediatag
{
    use DynamicProperty;

    public static function getTagIDbyValue($tag, $value)
    {
        $table = 'mediatag_' . $tag . '_ph';
        $name  = \strtolower(\str_replace(' ', '_', $value));

        $query = 'SELECT * FROM ' . $table . " WHERE star_name = '" . $name . "'";
        $res   = Storage::$DB->query($query);

        utmdd($res);
    }

    public static function updateMetaTagMap($video_id, $tag, $tag_id)
    {
        $table = 'mediatag_' . $tag . '_map';
// utmdump([$video_id,$tag,$tag_id]);
        $results = Storage::$DB->mysqllib->where('video_id', $video_id)
            ->where($tag . '_id', $tag_id)
            ->get($table);
        // utmdump(Storage::$DB->mysqllib->getLastQuery());
        if (count($results) < 1) {
            $data = ['video_id' => $video_id, $tag . '_id' => $tag_id];
            $id   = Storage::$DB->mysqllib->insert($table, $data);
        }
    }

    public static function updateArtistMap($videoId, $tag, $tagValue)
    {
        $res    = MetaTagInfo::getArtistByName($tag, $tagValue);
        // utmdump($res);
        $output = 'Updated Video id <file>' . $videoId . '</> with <info>' . $tagValue . '</> info ';
        foreach ($res as $row) {
            // utmdump($row);
            // if ($row['hide'] == 0) {
            //     //$output .= ' Value ' . $row['name'] . ' Id ' . $row['id'];
            $output .= PHP_EOL . "\t" . ' Name <comment>' . $row['star_name'] . '</> ';
            self::updateMetaTagMap($videoId, $tag, $row['id']);
            //     // $out[] = $output;
            // }
        }
        Mediatag::$Console->writeln($output);
        // utmdd("d");
    }

    public static function updateGenreMap($videoId, $tag, $tagValue)
    {
        $res    = MetaTagInfo::getGenreByName($tag, $tagValue);
        $output = 'Updated Video id <file>' . $videoId . '</> with <info>' . $tagValue . '</> info ';
        foreach ($res as $row) {
            // utmdump($row);
            if ($row['keep'] == 1) {
                //$output .= ' Value ' . $row['name'] . ' Id ' . $row['id'];
                $output .= PHP_EOL . "\t" . ' Name <comment>' . $row['genre'] . '</> ';
                self::updateMetaTagMap($videoId, $tag, $row['id']);
                // $out[] = $output;
            }
        }
        Mediatag::$Console->writeln($output);
    }

    public static function getArtistByName($tag, $name)
    {
        $results = null;
        $db      = MysqliDb::getInstance();
                        // utmdump(["Name"=>$name]);

        if (\str_contains($name, ',')) {
            $pcs = \explode(',', $name);
            foreach ($pcs as $k => $nameKey) {
                $r = self::getArtistfromDb($nameKey);
                // utmdump(["Name"=>$nameKey,"res"=>$r]);
                if (! is_null($r)) {
                    $results[] = $r;
                }
            }
        } else {
            $r = self::getArtistfromDb($name);
                if (! is_null($r)) {
                    $results[] = $r;
                }
        }

        // utmdump([__METHOD__ => $results]);
        return $results;
    }

    private static function getArtistfromDb($artist)
    {
        $db     = MysqliDb::getInstance();
        $result = null;
        $v      = \strtolower(str_replace(' ', '', $artist));
        $db->where('nameKey', $v, 'like');
        $oneRes = $db->getone(\__MYSQL_ARTIST_PH__);
        // utmdump($oneRes);
        if ($oneRes === null) {
            $db->where('nameKey', $v . '%', 'like');
            $res = $db->get(\__MYSQL_ARTIST_PH__);
            if (count($res) == 1) {
                if ($res[0]['gender'] != 'male') {
                    $result = $res[0];
                }
            }else if(count($res) == 0) {
                // utmdd([$db->getLastQuery(),$artist,count($res)]);
            } else {
                foreach ($res as $row) {
                    if ($row['gender'] != 'male') {
                        // utmdump([$artist, $row['star_name']]);
                    }
                }
            }
        } else {
            if ($oneRes['gender'] != 'male') {
                $result = $oneRes;
            }
        }
// utmdump([$result,$artist]);
        return $result;
        //     $data = ['name' => \strtolower($v), 'replacement' => ''];
        //     $id   = $db->insert(__MYSQL_ARTISTS__, $data);
        //     $res  = ['id'     => $id,
        //         'name'        => strtolower($v),
        //         'replacement' => '',
        //         'hide'        => 0,
        //         'isFemale'    => null];

        //     // utmdd([$v, $id, $db->getLastQuery()]);
        // }
        // $results[] = $res;
    }

    public static function getGenreByName($tag, $name)
    {
        $db = MysqliDb::getInstance();
        if (\str_contains($name, ',')) {
            $pcs = \explode(',', $name);
            foreach ($pcs as $k => $v) {
                $v = \str_replace(' ', '_', $v);
                $db->where('genre', $v, 'like', 'or');
            }
        } else {
            $db->where('genre', $name, 'like');
        }
        $res = $db->get(\__MYSQL_GENRE__);

        utmdump($res, $db->getLastQuery());

        return $res;
    }
}
