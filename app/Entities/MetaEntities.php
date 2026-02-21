<?php

namespace Mediatag\Entities;

use Mediatag\Entities\Tags\Episode;
use Mediatag\Entities\Tags\Movie;
use Mediatag\Entities\Tags\Scene;
use Symfony\Component\Finder\Finder;

class MetaEntities
{
    public static $tagName;

    public static $value;

    private static $ClassObj = null;

    private static $ClassUsePath = '\\Mediatag\\Entities\\Tags\\';

    private $ClassFiles = [];

    public $meta_tags = [];

    private static $ApOption = null;

    public function __construct($tagName = null, $value = null)
    {
        if ($tagName === null) {
            $classDir = __APP_HOME__ . '/app/Entities/Tags/';
            $finder   = new Finder;
            $finder->files()->in($classDir)->name('*.php');
            foreach ($finder as $file) {
                $this->ClassFiles[] = basename($file->getFilename(), '.php');
                $this->meta_tags[]  = strtolower(basename($file->getFilename(), '.php'));
            }
        } else {
            self::$tagName = $tagName;
            self::$value   = $value;
        }
    }

    public function init()
    {
        return $this;
        // $this->getCallbackArray();
        // utmdd($this->tagArray);
    }

    public function getCallbackArray()
    {
        $callbacks = [];
        foreach ($this->ClassFiles as $ClassName) {
            $Class = self::$ClassUsePath . ucfirst($ClassName);
            // utmdd(self::$ClassUsePath, ucfirst($ClassName));
            if (class_exists($Class)) {
                $callbacks[$ClassName] = $Class::MetaReaderCallback();
            }
        }

        return $callbacks;
    }

    private static function getEntityClass($tagName, $value = null)
    {
        $class = self::$ClassUsePath . ucfirst($tagName);
        if (class_exists($class)) {
            self::$ClassObj = new $class($tagName, $value);
        } else {
            self::$ClassObj = null;
        }
    }

    public static function CreateCmdOption($tagName, $value)
    {
        self::getEntityClass($tagName, $value);
        if (is_null(self::$ClassObj)) {
            return null;
        }

        return call_user_func([self::$ClassObj, 'GenerateOption']);

        // utmdd($options);
    }

    public static function CheckForTvParams($file)
    {
        $params = null;
        if (! is_null($file)) {
            $scene = Scene::isScene($file);
            if (count($scene) > 0) {
                $episode = Episode::isEpisode($file);
                if (count($episode) < 1) {
                    $episode = ['episode' => 1];
                }
                $Movie  = Movie::isMovie($file);
                $params = array_merge($scene, $episode, $Movie);
            }
        }

        return $params;
    }
}
