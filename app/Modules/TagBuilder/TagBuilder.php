<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\File\Reader as FileReader;
use Mediatag\Traits\MetaTags;
use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Option;

class TagBuilder
{
    use MetaTags;
    public static $dbConn;

    public $videoInfo;

    public $video_key;

    public $ReaderObj;

    public function __construct($key, $tagObj)
    {
        utminfo($key, get_class($tagObj));

        $this->video_key = $key;
        $this->ReaderObj = $tagObj;
    }

    public function getTags($videoInfo)
    {
        utminfo(func_get_args());
        $DbUpdates = null;
        // UTMlog::Logger('ReaderObj', $this->ReaderObj);
        if (! \defined('__UPDATE_SET_ONLY__')) {
            $updates     =  $this->ReaderObj->getFileValues();
            if (!str_starts_with($this->video_key, 'x')) {
                $jsonupdates = $this->ReaderObj->getJsonValues();
                $updates     = $this->mergetags($updates, $jsonupdates, $this->video_key);
            }
            $DbUpdates   = $this->ReaderObj->getDbValues();
// utmdd($DBUpdates);
        }
        if (null !== $DbUpdates) {
            $updates     = $this->mergetags($updates, $DbUpdates, $this->video_key);
        }
        if (isset($updates)) {
            // UTMlog::Logger('Reader', $updates);
        }

        foreach (Option::getOptions() as $option => $value) {
            $method = 'set' . $option;
            if (method_exists($this->ReaderObj, $method)) {
                // UTMlog::Logger('Option ' . $option, $method, $value);
                $updates[$option] = $this->ReaderObj->{$method}($value, $videoInfo['video_key']);

            }
        }

        // utmdd($updates);

        // UTMlog::Logger('updates', $updates);
        if (Option::isTrue('update')) {
            $updates['studio']        = $this->addNetwork($updates, $updates);
            $videoInfo['updateTags']  = $updates;
            $videoInfo['currentTags'] = [];
        } else {
            $current                  = $this->ReaderObj->getMetaValues();
            $videoInfo['currentTags'] = $current;
            foreach ($updates as $tag => $value) {
                if ($tag == 'studio') {
                    $updates[$tag] = $this->addNetwork($current, $updates);
                }
            }
            $videoInfo['updateTags']  = $this->compareTags($current, $updates);

        }
        return $videoInfo;
    }




    private function addNetwork($current, $updates)
    {

        $studio     = null;
        $tmpStudio  = null;
        $network    = null;
        $tmpNetwork = null;

        foreach ($current as $tag => $value) {
            if ($value === null) {
                unset($current[$tag]);
            }
        }
        foreach ($updates as $tag => $value) {
            if ($value === null) {
                unset($updates[$tag]);
            }
        }


        if (array_key_exists("studio", $current)) {
            $studio = $current['studio'];
        }

        if (array_key_exists("studio", $updates)) {
            $tmpStudio = $updates['studio'];
        }

        if (array_key_exists("network", $updates)) {
            $tmpNetwork = $updates['network'];

            if ($tmpNetwork !== null) {
                UtmDump([$tmpNetwork,$tmpStudio]);
                if ($tmpStudio != $tmpNetwork) {
                    $studio = $tmpStudio . "/" . $tmpNetwork;
                } else {
                $studio = $tmpStudio ;
                }
            }

        } else {
            if (array_key_exists("network", $current)) {
                $tmpNetwork = $current['network'];
                if ($tmpNetwork !== null) {
                    if ($tmpStudio != $tmpNetwork) {
                        $studio = $tmpStudio . "/" . $tmpNetwork;
                    }
                } else {
                    $studio = $tmpStudio ;
                }
            }else {
                $studio = $tmpStudio ;
            }
        }


        if (!isset($studio)) {
            utmdump( [$current, $updates, $tmpStudio]);
            return null;
        }
        $studio     = trim($studio, '/');
        return $studio;

    }
    // /**
    //  * @return array|mixed
    //  */
    // public function getTagValues()
    // {
    //     utmdd("afdsfasdfsd");

    //     utminfo(func_get_args());
    //     foreach (__META_TAGS__ as $tag) {
    //         if (\array_key_exists($tag, $tagList)) {
    //             if ('' == $tagList[$tag]) {
    //                 $getTags[] = $tag;
    //             } else {
    //                 $value         = $this->CleanMetaValue($tag, $tagList[$tag]);
    //                 $tagList[$tag] = $value;

    //                 if ('genre' == $tag && '' != $value) {
    //                     if (\array_key_exists('genre', $tagList)) {
    //                         if ('' != $tagList['genre']) {
    //                             $tagList['genre'] .= ',' . $value;
    //                         } else {
    //                             $tagList['genre'] = $value;
    //                         }
    //                         $tagList['genre'] .= ',' . $this->fileReader->getGenre();
    //                         $tagList['genre'] = $this->CleanMetaValue('genre', $tagList['genre']);
    //                     }
    //                 }

    //                 if ('studio' == $tag) {
    //                     $tagList['studio'] = $this->fileReader->getStudio() . '/' . $tagList['studio'];
    //                     $tagList['studio'] = $this->CleanMetaValue('studio', $tagList['studio']);
    //                     $tagList['studio'] = trim($tagList['studio'], '/');
    //                 }
    //             }
    //         } else {
    //             $getTags[] = $tag;
    //         }
    //     }

    //     if (\count($getTags) > 0) {
    //         foreach ($getTags as $tag) {
    //             $value = $this->getNewTagValue($tag);

    //             if ('' != $value) {
    //                 $value         = $this->CleanMetaValue($tag, $value);

    //                 $tagList[$tag] = $value;
    //             }
    //         }
    //     }

    //     return $tagList;
    // }

    private function compareTags(array $Current, array $New)
    {
        utminfo(func_get_args());

        $updates = [];
        foreach (__META_TAGS__ as $tag) {
            $current_tag    = $tag . '_current';
            ${$current_tag} = '';
            $new_tag        = $tag . '_new';
            ${$new_tag}     = '';
            if (\array_key_exists($tag, $Current)) {
                ${$current_tag} = $Current[$tag];
            }

            if (\array_key_exists($tag, $New)) {
                ${$new_tag} = $New[$tag];
            }
            if ('' != ${$new_tag}) {
                $lev           = levenshtein(${$current_tag}, ${$new_tag});
                $sim           = similar_text(${$current_tag}, ${$new_tag});
                $updates[$tag] = ${$new_tag};
                // // UTMlog::logNotice('Lev------------------------');
                // // UTMlog::logNotice('Lev Current', $$current_tag);
                // // UTMlog::logNotice('Lev New', $$new_tag);
                // // UTMlog::logNotice('Lev', $lev);
                // // UTMlog::logNotice('sim', $sim);
                // // UTMlog::logNotice('Lev------------------------');
                if (0 == $lev) {
                    unset($updates[$tag]);
                }
            } else {
                unset($updates[$tag]);
            }
        }

        return $updates;
    }
}
