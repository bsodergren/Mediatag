<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\TagBuilder;

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
        $this->video_key = $key;
        $this->ReaderObj = $tagObj;
    }

    public function getTags($videoInfo)
    {
        UTMLog::Logger('ReaderObj', $this->ReaderObj);

        if (! \defined('__UPDATE_SET_ONLY__')) {
            if (str_starts_with($this->video_key, 'x')) {
                $updates = $this->ReaderObj->getFileValues();
                // utmdump([__METHOD__.':'.__LINE__,"updates",$updates]);

            } else {
                $fileUpdates = $this->ReaderObj->getFileValues();
                UTMLog::Logger('fileUpdates', $fileUpdates);

                //  $fileUpdates['title'] = '';
                $jsonupdates = $this->ReaderObj->getJsonValues();
                UTMLog::Logger('jsonupdates', $jsonupdates);

                $updates = $this->mergetags($fileUpdates, $jsonupdates, $this->video_key);
            }
        }

        $DbUpdates = $this->ReaderObj->getDbValues();
        // utmdump([__METHOD__.':'.__LINE__,"dbupdate",$DbUpdates]);
        if (null !== $DbUpdates) {
            foreach ($DbUpdates as $tag => $value) {
                $updates[$tag] = $value;
            }
            // utmdump([__METHOD__.':'.__LINE__,$DbUpdates,$updates]);
        }

        if (isset($updates)) {
            UTMLog::Logger('Reader', $updates);
        }

        foreach (Option::getOptions() as $option => $value) {
            $method = 'set'.$option;
            if (method_exists($this->ReaderObj, $method)) {
                UTMLog::Logger('Option '.$option, $method, $value);
                $updates[$option] = $this->ReaderObj->{$method}($value, $videoInfo['video_key']);

            }
        }
        UTMLog::Logger('updates', $updates);

        if (Option::isTrue('update')) {
            $videoInfo['updateTags'] = $updates;
            $videoInfo['currentTags'] = [];
        } else {
            $current = $this->ReaderObj->getMetaValues();
            UTMLog::Logger('getMetaValues', $current);

            $videoInfo['currentTags'] = $current;
            foreach ($updates as $tag => $value) {
                if (str_starts_with($value, 'if:')) {
                    if (\array_key_exists($tag, $videoInfo['currentTags'])) {
                        if (true == $videoInfo['currentTags'][$tag]) {
                            unset($updates[$tag]);
                        } else {
                            $updates[$tag] = str_replace('if:', '', $value);
                        }
                    }
                }
            }

            $videoInfo['updateTags'] = $this->compareTags($current, $updates);
        }

        return $videoInfo;
    }

    /**
     * @return array|mixed
     */
    public function getTagValues()
    {
        foreach (__META_TAGS__ as $tag) {
            if (\array_key_exists($tag, $tagList)) {
                if ('' == $tagList[$tag]) {
                    $getTags[] = $tag;
                } else {
                    $value = $this->CleanMetaValue($tag, $tagList[$tag]);
                    $tagList[$tag] = $value;

                    if ('genre' == $tag && '' != $value) {
                        if (\array_key_exists('genre', $tagList)) {
                            if ('' != $tagList['genre']) {
                                $tagList['genre'] .= ','.$value;
                            } else {
                                $tagList['genre'] = $value;
                            }
                            $tagList['genre'] .= ','.$this->fileReader->getGenre();
                            $tagList['genre'] = $this->CleanMetaValue('genre', $tagList['genre']);
                        }
                    }

                    if ('studio' == $tag) {
                        $tagList['studio'] = $this->fileReader->getStudio().'/'.$tagList['studio'];
                        $tagList['studio'] = $this->CleanMetaValue('studio', $tagList['studio']);
                        $tagList['studio'] = trim($tagList['studio'], '/');
                    }
                }
            } else {
                $getTags[] = $tag;
            }
        }

        if (\count($getTags) > 0) {
            foreach ($getTags as $tag) {
                $value = $this->getNewTagValue($tag);

                if ('' != $value) {
                    $value = $this->CleanMetaValue($tag, $value);

                    $tagList[$tag] = $value;
                }
            }
        }

        return $tagList;
    }

    private function compareTags(array $Current, array $New)
    {
        $updates = [];
        foreach (__META_TAGS__ as $tag) {
            $current_tag = $tag.'_current';
            ${$current_tag} = '';
            $new_tag = $tag.'_new';
            ${$new_tag} = '';
            if (\array_key_exists($tag, $Current)) {
                ${$current_tag} = $Current[$tag];
            }

            if (\array_key_exists($tag, $New)) {
                ${$new_tag} = $New[$tag];
            }

            if ('' != ${$new_tag}) {
                $lev = levenshtein(${$current_tag}, ${$new_tag});
                $sim = similar_text(${$current_tag}, ${$new_tag});
                $updates[$tag] = ${$new_tag};
                // UTMLog::logNotice('Lev------------------------');
                // UTMLog::logNotice('Lev Current', $$current_tag);
                // UTMLog::logNotice('Lev New', $$new_tag);
                // UTMLog::logNotice('Lev', $lev);
                // UTMLog::logNotice('sim', $sim);
                // UTMLog::logNotice('Lev------------------------');
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
