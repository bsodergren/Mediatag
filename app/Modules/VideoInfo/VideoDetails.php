<?php

namespace Mediatag\Modules\VideoInfo;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Database\StorageDB;
use Mediatag\Traits\DynamicProperty;

class VideoDetails
{
    use DynamicProperty;

    public static $videoFields = [
        'VideoKey',
        'Library',
        'filename',
        'fullpath',
        'studio_path',
        'thumbnail',
        'preview',
        'filesize',
        'rating',
        'last_updated',
        'added',
        'new',
        'subLibrary',
        'FavoriteVideo',
    ];

    public static $metaFields = ['Library', 'subLibrary', 'title', 'genre', 'studio', 'network', 'artist', 'keyword'];

    public static $fileInfoFields = ['format', 'bit_rate', 'width', 'height', 'filesize', 'duration'];

    public static $db;
    //    public function videoid($video_id){
    //     self::videoid(;$video_id);
    //    }

    public static function __callStatic($method, $args)
    {
        if (strpos($method, 'set') === 0 || strpos($method, 'get') === 0) {
            $field  = substr($method, 3); //
            $method = substr($method, 0, 3); // 'set' or 'get'
            //  Remove 'set' prefix
            if (in_array($field, self::$videoFields)
                || in_array($field, self::$metaFields)
                || in_array($field, self::$fileInfoFields)) {
                return self::$method($field, ...$args);
            }
        }
    }

    public static function getVideoKey($video_id)
    {
        // Mediatag::$dbconn->where('id', $video_id)
        // utmdd();

        Storage::$DB->where('id', $video_id);
        $result = Storage::$DB->getOne(__MYSQL_VIDEO_FILE__, 'video_key');
        if ($result) {
            return $result['video_key'];
        }

        return null;
    }

    public static function set($field, $video_id, $params = [])
    {
        $videoKey = self::getVideoKey($video_id);

        $tables = self::getTableFromField($field);

        foreach ($tables as $table) {
            $tableAlias = explode(' as ', $table)[1] ?? $table;
            Storage::$DB->where($tableAlias . '.video_key', $videoKey);

            Storage::$DB->update($table, [$tableAlias . '.' . $field => $params]);
        }
    }

    public static function get($field, $video_id)
    {
        $videoKey = self::getVideoKey($video_id);

        $tables = self::getTableFromField($field);
        foreach ($tables as $table) {
            $tableAlias = explode(' as ', $table)[1] ?? $table;
            Storage::$DB->where($tableAlias . '.video_key', $videoKey);
            $result = Storage::$DB->getOne($table, $tableAlias . '.' . $field);
            if ($result) {
                return $result[$field];
            }
        }

        return null;
    }

    private static function getTableFromField($field)
    {
        $TableList = [];

        if (in_array($field, self::$videoFields)) {
            $TableList[] = __MYSQL_VIDEO_FILE__ . ' as v';
        }
        if (in_array($field, self::$metaFields)) {
            $TableList[] = __MYSQL_VIDEO_METADATA__ . ' as m';
        }
        if (in_array($field, self::$fileInfoFields)) {
            $TableList[] = __MYSQL_VIDEO_INFO__ . ' as i';
        }

        return $TableList;
    }
}
