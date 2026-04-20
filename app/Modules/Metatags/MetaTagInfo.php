<?php

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Traits\DynamicProperty;
use UTM\Bundle\mysql\MysqliDb;

class MetaTagInfo extends Mediatag
{
    use DynamicProperty;

    public static function updateMetaTagMap($video_id, $tag, $tag_id)
    {
        $db = MysqliDb::getInstance();

        $table = 'mediatag_' . $tag . '_map';

        $results = $db->where('video_id', $video_id)
            ->where($tag . '_id', $tag_id)
            ->get($table);
        if (count($results) < 1) {
            $data = ['video_id' => $video_id, $tag . '_id' => $tag_id];
            $id   = $db->insert($table, $data);
        }
    }

    public static function updateArtistMap($videoId, $tag, $tagValue)
    {
        $res    = MetaTagInfo::getArtistByName($tag, $tagValue);
        $output = 'Updated Video id <file>' . $videoId . '</> with <info>' . $tagValue . '</> info ';
        foreach ($res as $row) {
            utmdump($row);
            if ($row['hide'] == 0) {
                //$output .= ' Value ' . $row['name'] . ' Id ' . $row['id'];
                $output .= PHP_EOL . "\t" . ' Name <comment>' . $row['name'] . '</> ';
                self::updateMetaTagMap($videoId, $tag, $row['id']);
                // $out[] = $output;
            }
        }
        Mediatag::$Console->writeln($output);
    }

    public static function updateGenreMap($videoId, $tag, $tagValue)
    {
        $res    = MetaTagInfo::getGenreByName($tag, $tagValue);
        $output = 'Updated Video id <file>' . $videoId . '</> with <info>' . $tagValue . '</> info ';
        foreach ($res as $row) {
            utmdump($row);
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
        $db = MysqliDb::getInstance();
        if (\str_contains($name, ',')) {
            $pcs = \explode(',', $name);
            foreach ($pcs as $k => $v) {
                // $v = \str_replace(' ', '_', $v);
                $db->where('name', $v, 'like');
                $res = $db->getone(\__MYSQL_ARTISTS__);
                utmdump($res);
                if ($res === null) {
                    $data = ['name' => \strtolower($v), 'replacement' => ''];
                    $id   = $db->insert(__MYSQL_ARTISTS__, $data);
                    $res  = ['id'     => $id,
                        'name'        => strtolower($v),
                        'replacement' => '',
                        'hide'        => 0,
                        'isFemale'    => null];

                    // utmdd([$v, $id, $db->getLastQuery()]);
                }
                $results[] = $res;
            }
        } else {
            $db->where('name', $name, 'like');
            $results = $db->get(\__MYSQL_ARTISTS__);
        }

        return $results;
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
