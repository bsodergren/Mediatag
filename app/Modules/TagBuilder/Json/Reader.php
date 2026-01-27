<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\Json;

use Facebook\WebDriver\Exception\WebDriverException;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Utilities\MediaArray;
use Nette\Utils\FileSystem;
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
        $this->db = new TagDB;

        $this->expandArray($videoData);

        // utmdd($this->video_key);
        if ($this->getJsonFile()) {
            $this->json_array = json_decode($this->json_string, true);
        }
        unset($this->json_array['formats']);
        unset($this->json_array['http_headers']);
        unset($this->json_array['thumbnails']);
        unset($this->json_array['url']);
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

        // utmdump($tag);
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

            // utmdump($this->json_array);
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

    private function getJsonFile()
    {
        // utminfo(func_get_args());
        // if (! str_starts_with($this->video_key, 'x')) {
        $this->json_file = __JSON_CACHE_DIR__ . '/' . $this->video_key . '.info.json';

        if (file_exists($this->json_file)) {
            $this->json_string = MediaFilesystem::readLineNo($this->json_file, 1);
            // if ($this->video_key == 'ph5e8649773f814') {
            //     utmdd($this->json_string);
            // }

            if ($this->json_string == '') {
                $this->json_string = '{}';
            }

            return true;
        }

        return false;
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
