<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core\Helper;

use UTM\Utilities\Option;
use Mediatag\Core\Mediatag;

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


        Mediatag::$log->notice('addMeta');

        foreach (Option::getOptions() as $option => $v) {
            switch ($option) {
                case 'title':
                case 'genre':
                case 'artist':
                case 'keyword':
                case 'network':
                case 'studio':
                    if (!\defined('__UPDATE_SET_ONLY__')) {
                        \define('__UPDATE_SET_ONLY__', true);
                    }
                    $this->meta_tag_arrary = [$option];

                    break;
            }
        }

        if (true == Option::isTrue('only')) {
            $this->meta_tag_arrary = Option::getValue('only');
        }

        if (true == Option::isTrue('empty')) {
            if (null !== Option::getValue('empty', 1)) {
                $this->meta_tag_arrary = Option::getValue('empty');
            }
        }
        if (!\defined('__META_TAGS__')) {
            \define('__META_TAGS__', $this->meta_tag_arrary);
        }

        if (!\defined('TITLE_REPLACE_MAP')) {
            $this->getTitleMap('TITLE_REPLACE_MAP', Mediatag::$Storage->getTitleMap());
        }
      
    }

    
    public function setupMap()
    {
        if (!\defined('ARTIST_MAP')) {
            $this->getArtistMap('ARTIST_MAP', Mediatag::$Storage->getArtistMap());
        }

        if (!\defined('IGNORE_NAME_MAP')) {
            $this->getArtistMap('IGNORE_NAME_MAP', Mediatag::$Storage->getIgnoredArists());
        }

    }

    public function getTitleMap($constant, $file)
    {
        // utminfo([self::$index++ => [__FILE__,__LINE__,__METHOD__]]);

        if (\is_string($file)) {
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
        if (!\defined($constant)) {
            \define($constant, $nameArray);
        }
    }
}
