<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Patterns\Studios;

use Mediatag\Modules\Filesystem\MediaFilesystem as Filesystem;
use Mediatag\Utilities\MediaScraper;
use Mediatag\Modules\TagBuilder\Patterns;

const DIGITALPLAYGROUND_REGEX_COMMON = '//i';

class DigitalPlayground extends Patterns
{
    public $studio = 'Digital Playground';

    public $regex = [
        'digitalplayground' => [
            'title' => [
                'pattern' => '/([a-zA-Z-]+)(-(episode|scene)?([-0-9]{1,}))?\_[0-9]{0,10}/i',
                // 'pattern' => '/(([a-zA-Z0-9]+))\_s[0-9]{2,3}\_(.*)\_[0-9]{1,4}.*/i',
                'delim'   => '-',
                'match'   => 1,
            ],
        ],
    ];


    public function getTitle()
    {
        // utminfo(func_get_args());

        $regex = $this->getTitleRegex();
        if ($regex) {
            $success = preg_match($regex, $this->video_name, $output_array);

            if ($success != 0) {
                if (! array_key_exists($this->gettitleMatch(), $output_array)) {
                    return null;
                }

                $title      = str_replace($this->getTitleDelim(), ' ', $output_array[$this->gettitleMatch()]);
                $titleArray = explode(' ', $title);
                foreach ($titleArray as $key => $word) {
                    if (strtolower($word) === 's') {
                        $titleArray[$key - 1] .= strtolower($word);
                        unset($titleArray[$key]);
                    }
                    if (strtolower($word) === 'x') {
                        $titleArray[$key] = strtoupper($word . '-') . $titleArray[$key + 1];
                        unset($titleArray[$key + 1]);
                    }
                    if (strtolower($word) === 'episode' || strtolower($word) === 'scene') {
                        //                        $titleArray[$key] = strtoupper($word.'-') . $titleArray[$key+1];

                        if (array_key_exists('2', $output_array)) {
                            $output_array[2] = $word . $output_array[2];
                        }

                        unset($titleArray[$key]);
                    }
                }

                if (array_key_exists('2', $output_array)) {
                    $titleArray[] = trim($output_array[2], '-');
                }

                $title = implode(' ', $titleArray);
                $title = trim($title);

                return ucwords($title);
            }
        }

        return false;
    }
}
