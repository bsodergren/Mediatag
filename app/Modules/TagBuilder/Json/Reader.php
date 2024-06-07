<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder\Json;

use Facebook\WebDriver\Exception\WebDriverException;
use Mediatag\Core\MediaCache;
use Mediatag\Modules\Database\TagDB;
use Mediatag\Modules\Executable\YoutubeExec;
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
        $this->db = new TagDB();

        $this->expandArray($videoData);
        if ($this->getJsonFile()) {
            $this->json_array = json_decode($this->json_string, true);
        }
    }

    public function __call($method, $arguments)
    {
        UTMLog::logger('Call in json', $method);

        $this->get($method);
    }

    public function artist()
    {
        $this->title();

        if (\array_key_exists('title', $this->tag_array)) {
            $string = $this->tag_array['title'];
            //
            $string = $this->matchArtist($string);
            UTMLog::logger('title in artist()', $string);
            $this->tag_array['artist'] = $string;
        }

        return null;
    }

    private function getPageDetails($webpage_url)
    {
        $key = 'ph_webpage_'.ltrim(strrchr($webpage_url, '='), '=');
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
        $tag = strtolower($tag);

        if (! \array_key_exists($tag, $this->tag_array)) {
            $json_key = $tag;
            if ('genre' == $tag) {
                $json_key = 'categories';
            }
            if ('studio' == $tag) {
                $json_key = 'uploader';
            }
            if ('keyword' == $tag) {
                $json_key = 'tags';
            }

            if (\array_key_exists($json_key, $this->json_array)) {
                $value = $this->json_array[$json_key];
                if ('categories' == $json_key) {
                    $keyword_value = $this->json_array['tags'];
                    $value = array_merge($value, $keyword_value);
                }

                if (\is_array($value)) {
                    $value = implode(',', $value);
                }

                if ('studio' == $tag) {
                    $value = ucwords($value);
                    utmdump([$tag,$value]);

                }

                UTMLog::Logger('json data '.$tag, $value);
                $this->tag_array[$tag] = $value;
            }
        }
    }

    private function getJsonFile()
    {


        $this->json_file = __JSON_CACHE_DIR__.'/'.$this->video_key.'.info.json';

        if (file_exists($this->json_file)) {
            $this->json_string = FileSystem::read($this->json_file);

            return true;
        } else {
            $exec = new YoutubeExec('');
            $exec->youtubeGetJson($this->video_key);

            $this->json_file = __JSON_CACHE_DIR__.'/'.$this->video_key.'.info.json';

            if (file_exists($this->json_file)) {
                $this->json_string = FileSystem::read($this->json_file);

                return true;
            }

        }

        return false;
    }

    private function matchArtist($string)
    {
        // $artists_array = $this->getPageDetails($this->json_array['webpage_url']);

        $string = strtolower($string);

        $string = str_replace(' ', '_', $string);
        // //   $name_key = str_replace('.', '', $name_key);

        $res = MediaArray::matchArtist(ARTIST_MAP, $string);

        if (null !== $res) {
            // if (is_array($artists_array))
            // {
            foreach ($res as $names) {
                $names = str_replace('_', ' ', $names);
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
