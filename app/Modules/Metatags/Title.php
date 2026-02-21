<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Metatags;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\TagBuilder\TagBuilder;
use Mediatag\Utilities\Strings;
use UTM\Bundle\Monolog\UTMLog;

class Title extends TagBuilder
{
    public function __construct($videoData)
    {
        // utminfo(func_get_args());

        // // UTMlog::Logger('data', $this->videoData);
    }

    public static function writeTagList($text, $file = false)
    {
        // utminfo(func_get_args());

        $file = Mediatag::$titleList;

        return parent::writeTagList($text, $file);
    }

    public function getTagValue() {}

    public static function clean($clean_text, $isFile = false)
    {
        // utminfo(func_get_args());

        //        $genObj = new Genre();
        /*
                $first = explode(" ",$text);
                $out         = exec('trans -no-ansi -no-warn -id '.$first[0]);
                if (!str_contains($out, "English"))
                {
                 $text = Strings::translate($text," ");
                }
        */
        // $text      = strtolower($text);
        // UTMlog::Logger('before', $text);

        $text = Strings::clean($clean_text);

        $text      = str_replace("\\'", "'", $text);
        $text      = str_replace('/', ' ', $text);
        $text      = str_replace('(', ' ', $text);
        $text      = str_replace(')', ' ', $text);
        $text      = str_replace(', ', ',', $text);
        $text      = str_replace(',,', '', $text);
        $titleText = trim($text);

        // utmdd([__FILE__, __METHOD__, __LINE__]);

        if ($isFile === false) {
            $r = false;
            // utmdd([__FILE__, __METHOD__, __LINE__]);
            foreach (TITLE_REPLACE_MAP as $filter) {
                $filter = strtolower($filter);
                if (str_contains(strtolower($text), $filter)) {
                    $titleText = str_ireplace($filter, '', $titleText);
                    $titleText = trim($titleText);

                    if (str_starts_with($titleText, '-')) {
                        $titleText = trim($titleText, '-');
                    }
                    if (str_starts_with($titleText, '_')) {
                        $titleText = trim($titleText, '_');
                    }

                    if (str_ends_with($titleText, '-')) {
                        $titleText = trim($titleText, '-');
                    }
                    if (str_ends_with($titleText, '_')) {
                        $titleText = trim($titleText, '_');
                    }
                    $titleText = str_ireplace('  ', ' ', $titleText);

                    $titleText = trim($titleText);
                    $r         = true;
                }
            }
            // utmdump([$text, $titleText, $clean_text]);
        }
        if (! isset($titleText)) {
        }

        if ($r === false) {
            //  self::writeTagList($titleText);
        }
        // UTMlog::Logger('after', $titleText);

        Mediatag::notice("CleanTitle '{title}'", ['title' => $titleText]);

        return $titleText;
    }
}
