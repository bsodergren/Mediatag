<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\Json;

use Facebook\WebDriver\Exception\WebDriverException;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Database\Storage;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Utilities\MediaArray;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Panther\Client;
use UTM\Bundle\Monolog\UTMLog;

use function array_key_exists;
use function is_array;

class Reader extends TagReader
{
    public $tag_array = [];

    public $db;

    private $json_file;

    private $json_string = '{}';

    private $json_array = [];

    public function __construct($videoData)
    {
        // utminfo(func_get_args());
        $this->db = Storage::$DB;

        $this->expandArray($videoData);

        if ($this->getJsonFile()) {
            $this->json_array = json_decode($this->json_string, true);
            $this->json_array = $this->convertJson($this->json_array);
        }

        unset($this->json_array['formats']);
        unset($this->json_array['http_headers']);
        unset($this->json_array['thumbnails']);
        unset($this->json_array['url']);
    }

    private function convertJson($json)
    {
        if (is_string($json)) {
            $json = json_decode($json, JSON_OBJECT_AS_ARRAY);
        }

        $newJson = [];
        $map     = [
            'VideoName'  => 'title',
            'Title'      => 'title',
            'Actors'     => 'cast',
            'Genre'      => ['categories', 'tags'],
            'Studio'     => 'uploader',
            'Network'    => 'extractor',
            'title'      => 'title',
            'cast'       => 'cast',
            'tags'       => 'tags',
            'categories' => 'categories',
            'uploader'   => 'uploader',
            'actionTags' => 'actionTags',
            'channel'    => 'channel',
            'series'     => 'series',
            'duration'   => 'duration',
            'extractor'  => 'extractor',
        ];
        // $text                = urldecode($this->postArray['text']);
        // $array2              = \json_decode($text, \JSON_OBJECT_AS_ARRAY);
        // $videoLength         = $array2['VideoLen'];
        // $newJson['duration'] = $this->timeCalculator($videoLength, 100);
        // $actionTags          = [];
        // foreach ($array2['Markers'] as $n => $line) {
        //     $timeCode = $this->timeCalculator($videoLength, $line['Position']);
        //     unset($array2['Markers'][$n]['Position']);
        //     $actionTags[] = $line['Marker'] . ':' . $timeCode;
        // }
        // $newJson['actionTags'] = \implode(',', $actionTags);

        foreach ($map as $oldKey => $newKey) {
            if (array_key_exists($oldKey, $json)) {
                if (is_array($newKey)) {
                    foreach ($newKey as $nk) {
                        $newJson[$nk] = $json[$oldKey];
                    }
                } else {
                    $newJson[$newKey] = $json[$oldKey];
                }
            }
        }

        return $newJson;
    }

    public function __call($method, $arguments)
    {
        // utminfo(func_get_args());
        // UTMlog::logger('Call in json', $method);

        $this->get($method);

        return $this->tag_array;
    }

    public function titleArtist()
    {
        // utminfo(func_get_args());

        $this->title();
        if (array_key_exists('title', $this->tag_array)) {
            $string                    = $this->tag_array['title'];
            $string                    = $this->matchArtist($string);
            $this->tag_array['artist'] = $string;
        }

        return null;
    }

    private function getPageDetails($webpage_url)
    {
        // utminfo(func_get_args());

        $key          = 'ph_webpage__' . ltrim(strrchr($webpage_url, '='), '=');
        $artist_array = MediaCache::get($key);

        if ($artist_array === false) {
            $client = Client::createChromeClient();

            $webpage_url = str_replace('pornhubpremium.com', 'pornhub.com', $webpage_url);

            $client->request('GET', $webpage_url);

            //  $crawler     = $client->waitFor('.pornstarsWrapper');
            // Alternatively, wait for an element to be visible
            try {
                $crawler = $client->waitFor('.pornstarsWrapper', 3, 100);
            } catch (WebDriverException $e) {
                MediaCache::put($key, []);

                return null;
            }

            $artist_array = $crawler->filter('.pstar-list-btn')->each(function ($node, $i) {
                return $node->text();
            });

            $r = MediaCache::put($key, $artist_array);
        }

        return $artist_array;
    }

    private function get($tag)
    {
        // utminfo(func_get_args());

        $tag = strtolower($tag);
        if ($tag == 'genre') {
            $this->getJsonValue($tag, ['tags', 'categories']);
        }
        if ($tag == 'title') {
            $this->getJsonValue($tag, ['title']);
        }
        if ($tag == 'studio') {
            $this->getJsonValue($tag, ['uploader', 'channel', 'series']);
        }
        if ($tag == 'keyword') {
            $this->getJsonValue($tag, ['tags', 'categories']);
        }
        if ($tag == 'artist') {
            $this->getJsonValue($tag, ['cast']);
        }
        if ($tag == 'network') {
            $this->getJsonValue(
                $tag,
                ['extractor'],
                [
                    'exclude' => ['PornHub'],
                    'rename'  => ['NubilesPorn' => 'Nubiles'],
                ]
            );
        }
        if ($tag == 'actiontags') {
            $this->getJsonValue($tag, ['actionTags']);
        }
    }

    private function getJsonValue($tag, $keyList = [], $options = [])
    {
        if (! is_array($keyList)) {
            $keyList[] = $keyList;
        }

        foreach ($keyList as $json_key) {
            if ($tag == 'artist') {
                // // utmdump(['artist', $this->json_array['cast']]);
            }
            //
            if (array_key_exists($json_key, $this->json_array)) {
                $value = $this->json_array[$json_key];
                if ($tag == 'studio') {
                }
                if ($json_key == 'categories') {
                    $keyword_value = $this->json_array['tags'];
                    $value         = array_merge($value, $keyword_value);
                }

                if (is_array($value)) {
                    $value = implode(',', $value);
                } else {
                    // if ('studio' == $tag) {
                    $value = ucwords($value);
                }
                // // utmdump([$tag,$value]);
                // UTMlog::Logger('json data ' . $tag, $value);
                $this->tag_array[$tag] = $value;
                // utmdd($this->tag_array[$tag]);

                if (array_key_exists('exclude', $options)) {
                    foreach ($options['exclude'] as $string) {
                        $this->tag_array[$tag] = str_replace($string, '', $this->tag_array[$tag]);
                    }
                }

                if (array_key_exists('rename', $options)) {
                    foreach ($options['rename'] as $key => $string) {
                        $this->tag_array[$tag] = str_replace($key, $string, $this->tag_array[$tag]);
                    }
                }

                if ($this->tag_array[$tag] == '') {
                    $this->tag_array[$tag] = null;
                }
            }

            if ($tag == 'artist') {
                if (! isset($this->tag_array['artist'])) {
                    $this->titleArtist();
                }
            }
        }
    }

    private function moveJsontoCache($file)
    {
        $newFile = __STUDIO_JSON_CACHE_DIR__ . '/' . $this->video_key . '.info.json';
        if (! file_exists($newFile)) {
            FileSystem::copy($file, $newFile);
            \unlink($file);
        }

        return $newFile;
    }

    public static function checkJsonForUpdate($json_file, $video_key)
    {
        $fileLocation = __PLEX_STUDIO_JSON_DIR__ . DIRECTORY_SEPARATOR . $video_key . '.info.json';

        if (\file_exists($fileLocation)) {
            $json_string = MediaFilesystem::readLineNo($json_file, 1);
            $file_string = MediaFilesystem::readLineNo($fileLocation, 1);

            $jsonArray = \json_decode($json_string, 1);
            $fileArray = \json_decode($file_string, 1);

            $diff = \array_diff_assoc($fileArray, $jsonArray);
            if (count($diff) > 0) {
                $newArray = [];
                $jsonKeys = \array_keys($jsonArray);

                foreach ($jsonKeys as $key) {
                    if (array_key_exists($key, $fileArray)) {
                        if (is_array($fileArray[$key]) && is_array($jsonArray[$key])) {
                            $array          = \array_merge($fileArray[$key], $jsonArray[$key]);
                            $array          = \array_unique($array);
                            $newArray[$key] = $array;
                        } else {
                            if (Strings::compare($fileArray[$key], $jsonArray[$key])) {
                                $newArray[$key] = $jsonArray[$key];
                            } elseif (Strings::compare($jsonArray[$key], '')) {
                                $newArray[$key] = $fileArray[$key];
                            } elseif (Strings::compare($fileArray[$key], '')) {
                                $newArray[$key] = $jsonArray[$key];
                            }
                        }
                        unset($fileArray[$key]);
                        unset($jsonArray[$key]);
                    }
                }
                $newArray = \array_merge($newArray, $jsonArray, $fileArray);
                $string   = json_encode($newArray);
                // utmdd($string);
                MediaFilesystem::writeFile($json_file, $string);
                unlink($fileLocation);
            }
        }

        return $json_file;
    }

    private function getJsonFile()
    {
        // utminfo(func_get_args());
        // if (! str_starts_with($this->video_key, 'x')) {
        $this->json_file = null;
        $video_key       = null;

        $locationMap = [
            __JSON_CACHE_DIR__,
            __STUDIO_JSON_CACHE_DIR__, ];
        $extMap = ['.info.json', '.json'];

        foreach ($locationMap as $location) {
            foreach ($extMap as $ext) {
                $fileLocation = $location . DIRECTORY_SEPARATOR . $this->video_key . $ext;
                if (file_exists($fileLocation)) {
                    $this->json_file = $fileLocation;
                    $video_key       = $this->video_key;
                    // return true;
                }
            }
        }
        // utmdump($this->json_file);
        if ($this->json_file === null) {
            $files = MediaFinder::find('*.json', __PLEX_STUDIO_JSON_DIR__, quiet: true);
            foreach ($files as $file) {
                $json_key         = basename($file, '.json');
                $json_key         = basename($json_key, '.info');
                $json_key         = \strtolower(str_replace('_', '', $json_key));
                $this->video_name = strtolower($this->video_name);
                // utmdump(['Video Key' => [$this->video_key, $json_key, $file]]);
                if (str_contains($this->video_key, $json_key)) {
                    $this->json_file = $this->moveJsontoCache($file);
                    $video_key       = $json_key;
                    break;
                }

                // utmdump(['VideoName' => [$this->video_name, $json_key, $file]]);
                if (\str_contains($this->video_name, $json_key)) {
                    $this->json_file = $this->moveJsontoCache($file);
                    $video_key       = $json_key;
                    break;
                }
            }
            if (! file_exists($this->json_file)) {
                return false;
            }
        }

        // utmdump(['Json exists' => $this->json_file]);
        $this->json_file = self::checkJsonForUpdate($this->json_file, $video_key);

        $this->json_string = MediaFilesystem::readLineNo($this->json_file, 1);
        // if ($this->video_key == 'ph5e8649773f814') {
        // }
        if ($this->json_string == '') {
            $this->json_string = '{}';
        }

        return true;
    }

    private function matchArtist($string)
    {
        // utminfo(func_get_args());

        // $artists_array = $this->getPageDetails($this->json_array['webpage_url']);

        $string = strtolower($string);

        $string = str_replace(' ', '_', $string);
        // //   $name_key = str_replace('.', '', $name_key);

        $res = MediaArray::matchArtist(ARTIST_MAP, $string);
        if ($res === null) {
            // // utmdump($string);
        }
        if ($res !== null) {
            // if (is_array($artists_array))
            // {
            foreach ($res as $names) {
                $names       = str_replace('_', ' ', $names);
                $nameArray[] = ucwords($names);
            }

            return implode(', ', $nameArray);
            // } else
            // {
            //     $artistName = $res;
        }
        // }

        return null;
    }
}
