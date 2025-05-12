<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\Json;

use Facebook\WebDriver\Exception\WebDriverException;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\Executable\Youtube;
use Mediatag\Modules\TagBuilder\TagReader;
use Mediatag\Utilities\MediaArray;
use Nette\Utils\FileSystem;
use Symfony\Component\Panther\Client;
use UTM\Bundle\Monolog\UTMLog;

class Reader extends TagReader
{
    public $tag_array = [];

    public $db;

    private $json_file;

    private $json_string;

    private $json_array = [];

    public function __construct($videoData)
    {
        // utminfo(func_get_args());
        $this->db = new TagDB();

        $this->expandArray($videoData);

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
        if (\array_key_exists('title', $this->tag_array)) {
            $string                    = $this->tag_array['title'];
            $string                    = $this->matchArtist($string);
            $this->tag_array['artist'] = $string;
        }

        return null;
    }

    private function getPageDetails($webpage_url)
    {
        // utminfo(func_get_args());

        $key          = 'ph_webpage_'.ltrim(strrchr($webpage_url, '='), '=');
        $artist_array = MediaCache::get($key);

        if (false === $artist_array) {
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
        if ('genre' == $tag) {
            $this->getJsonValue($tag, ['tags', 'categories']);
        }
        if ('title' == $tag) {
            $this->getJsonValue($tag, ['title']);
        }
        if ('studio' == $tag) {
            $this->getJsonValue($tag, ['uploader', 'channel']);
        }
        if ('keyword' == $tag) {
            $this->getJsonValue($tag, ['tags', 'categories']);
        }
        if ('artist' == $tag) {
            $this->getJsonValue($tag, ['cast']);
        }
        if ('network' == $tag) {
            $this->getJsonValue(
                $tag,
                ['extractor'],
                [
                    'exclude' => ['PornHub'],
                    'rename' => ['NubilesPorn' => 'Nubiles'],
                ]
            );
        }
    }

    private function getJsonValue($tag, $keyList = [], $options = [])
    {
        if (!\is_array($keyList)) {
            $keyList[] = $keyList;
        }

        foreach ($keyList as $json_key) {
            if ('artist' == $tag) {
                // utmdump(['artist', $this->json_array['cast']]);
            }
            if (\array_key_exists($json_key, $this->json_array)) {
                $value = $this->json_array[$json_key];
                if ('studio' == $tag) {
                    // utmdump([$value, $json_key, $tag]);
                }
                if ('categories' == $json_key) {
                    $keyword_value = $this->json_array['tags'];
                    $value         = array_merge($value, $keyword_value);
                }

                if (\is_array($value)) {
                    $value = implode(',', $value);
                } else {
                    // if ('studio' == $tag) {
                    $value = ucwords($value);
                }
                // utmdump([$tag,$value]);
                // UTMlog::Logger('json data ' . $tag, $value);
                $this->tag_array[$tag] = $value;
                // utmdd($this->tag_array[$tag]);

                if (\array_key_exists('exclude', $options)) {
                    foreach ($options['exclude'] as $string) {
                        $this->tag_array[$tag] = str_replace($string, '', $this->tag_array[$tag]);
                    }
                }

                if (\array_key_exists('rename', $options)) {
                    foreach ($options['rename'] as $key => $string) {
                        $this->tag_array[$tag] = str_replace($key, $string, $this->tag_array[$tag]);
                    }
                }

                if ('' == $this->tag_array[$tag]) {
                    $this->tag_array[$tag] = null;
                }
            }

            if ('artist' == $tag) {
                if (!isset($this->tag_array['artist'])) {
                    $this->titleArtist();
                }
            }
        }

        // utmdump([$tag,$this->tag_array[$tag]]);
    }

    private function getJsonFile()
    {
        // utminfo(func_get_args());
        if (!str_starts_with($this->video_key, 'x')) {
            $this->json_file = __JSON_CACHE_DIR__.'/'.$this->video_key.'.info.json';

            if (file_exists($this->json_file)) {
                $this->json_string = FileSystem::read($this->json_file);


                if ('' == $this->json_string) {
                    return false;
                }

                return true;
            } else {
                $exec = new Youtube(__LIBRARY__);

                $exec->youtubeGetJson($this->video_key);

                $this->json_file = __JSON_CACHE_DIR__.'/'.$this->video_key.'.info.json';

                if (file_exists($this->json_file)) {
                    $this->json_string = FileSystem::read($this->json_file);
                    // utmdd($this->json_string);

                    return true;
                } else {
                    touch($this->json_file);
                }
            }
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
        if (null === $res) {
            // utmdump($string);
        }
        if (null !== $res) {
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
