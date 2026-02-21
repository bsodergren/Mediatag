<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use Mediatag\Core\Mediatag;
use Mediatag\Entities\MetaEntities;
use UTM\Utilities\Option;

use function define;
use function defined;
use function is_array;
use function is_string;

trait MediaExecute
{
    private $meta_tag_arrary = [
        'studio',
        'genre',
        'artist',
        'title',
        'keyword',
        'network',
    ];

    public function addMeta()
    {
        Mediatag::notice('addMeta', __FILE__);

        $ent                   = new MetaEntities;
        $this->meta_tag_arrary = $ent->meta_tags;

        foreach (Option::getOptions() as $option => $v) {
            switch ($option) {
                case 'title':
                case 'genre':
                case 'artist':
                case 'keyword':
                case 'network':
                case 'studio':
                    if (! defined('__UPDATE_SET_ONLY__')) {
                        define('__UPDATE_SET_ONLY__', true);
                    }
                    $this->meta_tag_arrary = [$option];

                    break;
            }
        }

        if (Option::isTrue('only') == true) {
            $this->meta_tag_arrary = Option::getValue('only');
        }

        if (Option::isTrue('empty') == true) {
            if (Option::getValue('empty', 1) !== null) {
                $this->meta_tag_arrary = Option::getValue('empty');
            }
        }
        if (! defined('__META_TAGS__')) {
            define('__META_TAGS__', $this->meta_tag_arrary);
        }

        if (! defined('TITLE_REPLACE_MAP')) {
            $this->getTitleMap('TITLE_REPLACE_MAP', Mediatag::$Storage->getTitleMap());
        }
        if (! defined('ARTIST_MAP')) {
            $this->mapArtist('ARTIST_MAP', Mediatag::$Storage->getArtistMap());
        }
    }

    public function setupMap()
    {
        Mediatag::notice('setupMap', __FILE__);

        if (! defined('ARTIST_MAP')) {
            $this->mapArtist('ARTIST_MAP', Mediatag::$Storage->getArtistMap());
        }

        if (! defined('IGNORE_NAME_MAP')) {
            $this->mapArtist('IGNORE_NAME_MAP', Mediatag::$Storage->getIgnoredArists());
        }
    }

    public function getTitleMap($constant, $file)
    {
        Mediatag::notice('getTitleMap', __FILE__);
        Mediatag::debug('getTitleMap', __FILE__);

        if (is_string($file)) {
            if (is_file($file)) {
                $artistList = file_get_contents($file);

                $artistMap = explode("\n", $artistList);
            }
        } else {
            $artistMap = $file;
        }

        foreach ($artistMap as $name) {
            $name        = trim($name);
            $nameArray[] = strtolower($name);
        }

        sort($nameArray);
        array_unique($nameArray);
        if (! defined($constant)) {
            define($constant, $nameArray);
        }
    }

    public function mapArtist($constant, $file)
    {
        // utminfo(func_get_args());
        Mediatag::notice('mapArtist', __FILE__);

        $replacement = null;
        if (is_string($file)) {
            if (is_file($file)) {
                $artistList = file_get_contents($file);

                $artistMap = explode("\n", $artistList);
            }
        } else {
            $artistMap = $file;
        }

        foreach ($artistMap as $key => $nameArray) {
            if (is_array($nameArray)) {
                $replacement = trim($nameArray[1]);
                $replacement = str_replace(' ', '_', $replacement);

                $name      = trim($nameArray[0]);
                $name      = str_replace(' ', '_', $name);
                $nameMap[] = ['name' => strtolower($name), 'replacement' => $replacement];
            } else {
                $nameMap[] = strtolower(str_replace(' ', '_', $nameArray));
            }
        }
        define($constant, $nameMap);
    }
}
