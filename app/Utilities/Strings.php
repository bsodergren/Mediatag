<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Utilities;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Filesystem\MediaFile as File;

class Strings extends \Nette\Utils\Strings
{
    private static $dumpString = '';

    public static function map($value, $fromLow, $fromHigh, $toLow, $toHigh)
    {
        $fromRange   = $fromHigh - $fromLow;
        $toRange     = $toHigh     - $toLow;
        $scaleFactor = $toRange / $fromRange;

        // Re-zero the value within the from range
        $tmpValue = $value - $fromLow;
        // Rescale the value to the to range
        $tmpValue *= $scaleFactor;

        // Re-zero back to the to range
        return $tmpValue + $toLow;
    }

    public static function videoDuration($duration)
    {
        // utminfo(func_get_args());

        $seconds = round($duration / 1000);
        $hours   = floor($seconds / 3600);

        $minutes = round((float) $seconds / 60 % 60);

        $sec = round($seconds % 60);

        return \sprintf('%02d:%02d:%02d', $hours, $minutes, $sec);
    }

    public static function clean($text)
    {
        // utminfo(func_get_args());

        if ('' == $text) {
            return $text;
        }

        return self::cleanSpecialChars($text);
    }

    public static function cleanFileName($filename)
    {
        // utminfo(func_get_args());

        if ('' == $filename) {
            return $filename;
        }

        $video_key = File::File($filename, 'videokey');

//         if (str_starts_with($video_key, 'x')) {
//             utmdd($filename);

//             return $filename;
//         }
// utmdd(__METHOD__);
        $fileInfo = pathinfo($filename);
        $filename = $fileInfo['filename'];

        if (str_contains($filename, $video_key)) {
            $filename  = str_replace('-'.$video_key, '', $fileInfo['filename']);
            $video_key = '-'.$video_key;
        } else {
            $video_key = '';
        }

        $fileExt = $fileInfo['extension'];

        $filename = self::cleanSpecialChars($filename, true);

        return $filename.$video_key.'.'.$fileExt;
    }

    public static function truncateString($string, $maxlength, $ellipsis = false, $reverse = false)
    {
        // utminfo(func_get_args());

        if (mb_strlen($string) <= $maxlength) {
            return $string;
        }
        $color_length = 0;
        $color_close  = '';
        if (str_contains($string, "\033[0m")) {
            $string       = str_replace("\033[0m", '', $string);
            $color_length = mb_strlen("\033[0m");
            $color_close  = "\033[0m";
        }

        if (empty($ellipsis)) {
            $ellipsis = '';
        }

        if (true === $ellipsis) {
            $ellipsis = '…';
        }

        $ellipsis_length = mb_strlen($ellipsis);

        $maxlength = $maxlength - $ellipsis_length - $color_length;

        return trim(mb_substr($string, 0, $maxlength)).$ellipsis.$color_close;
    }

    public static function showStatus($done, $total, $size = 30, $label = '')
    {
        // utminfo(func_get_args());

        return self::showStatusBar($done, $total, $size, $label);
    }

    public static function showStatusBar($done, $total, $size = 30, $label = '')
    {
        // utminfo(func_get_args());

        //  static $start_time;

        $label = self::truncateString($label, 45, true);

        // if we go over our bound, just ignore it
        if ($done > $total) {
            echo \PHP_EOL;

            return 0;
        }

        //   if(empty($start_time)) $start_time=time();
        //   $now = time();

        $perc = (float) ($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r[".$label;
        $status_bar .= ' '.number_format($done).'/'.number_format($total).' ';

        $str_len = \strlen($status_bar);
        $size -= $str_len;
        $bar = floor($perc * $size);
        if ($bar < 1) {
            $bar = 0;
        }
        $status_bar .= str_repeat('=', $bar);
        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] {$disp}%";
        echo $status_bar;

        // flush();

        // when done, send a newline
        if ($done == $total || 0 == $done) {
            echo \PHP_EOL;

            return 0;
        }
    }

    public static function geturl($string)
    {
        // utminfo(func_get_args());

        $array = explode('"', $string);

        return $array[1];
    }

    public static function getkey($string)
    {
        // utminfo(func_get_args());

        $array = explode('/', $string);

        return $array[6];
    }

    public static function wrapimplode($array, $before = '', $after = '', $separator = '')
    {
        // utminfo(func_get_args());

        if (!$array) {
            return '';
        }

        return $before.implode("{$after}{$separator}{$before}", $array).$after;
    }

    public static function translate($text, $sep = '_')
    {
        // utminfo(func_get_args());

        return $text;
    }

    private static function cleanSpecialChars($text, $file = false)
    {
        // utminfo(func_get_args());
        Mediatag::$log->notice('Getting text value, {text}', ['text'=>$text]);

        $file_special_chars = [];
        $special_chars      = ['?', '[', '´', ']', '/', '\\', '=', '<', '>', ':',
             '"', '&', '$', '#', '*', '|', '`', '!', '{', '}',
            '%',  '«', '»', '”', '“', \chr(0)];

        if (true === $file) {

            $file_special_chars = ['.', ';', ','];
            
        } else {
            $file_special_chars = ['’', "'"];

           
        }
        $special_chars      = array_merge($special_chars, $file_special_chars);
        $text = str_replace('é', 'e', $text);

        if (true === $file) {
            $text = strtolower($text);
            $text = str_replace(["’","'"], '', $text);
            $text = str_replace($file_special_chars, '_', $text);
            $text = str_replace('__', '_', $text);


        }

        foreach (str_split($text) as $char) {
            // Mediatag::$log->notice('Character {char}, {ord}', ['char'=>$char,'ord'=>ord($char) ]);
            if (\ord($char) > 125) {
                $str[] = ' ';
            } else {
                $str[] = $char;
            }

        }
       

        $text = implode('', $str);
        // Mediatag::$log->notice('Getting text value, {text}', ['text'=>$text]);

    
        // Mediatag::$log->notice('Getting text value, {text}', ['text'=>$text]);

        $text          = str_replace($special_chars, '', $text);
        $special_chars = ['(', ')', '~'];
        $text          = str_replace($special_chars, ' ', $text);
        $text          = str_replace(['%20', '+'], '-', $text);
        $text          = preg_replace('/[\r\n\t ]+/', '_', $text);
        $text          = str_replace('_', ' ', $text);
        if (true === $file) {
            $text = ucwords($text);
            $text = str_replace(' ', '_', $text);
            $text = str_replace('-', ' ', $text);
            $text = ucwords($text);
            $text = str_replace(' ', '-', $text);
            $text = str_replace('___', '_', $text);

        }

        $text = str_replace('__', '_', $text);
        Mediatag::$log->notice('Getting text value, {text}', ['text'=>$text]);

        return trim($text, '.-_');
    }

    public static function getFilePath($filename)
    {
        // utminfo(func_get_args());

        return str_replace(__PLEX_HOME__.\DIRECTORY_SEPARATOR.__LIBRARY__.\DIRECTORY_SEPARATOR, '', $filename);
    }

    public static function StudioName($name, $forward = true)
    {
        if (true === $forward) {
            $name = str_replace('1000', 'Thousand', $name);
            $name = str_replace('21st', 'TwentyFirst', $name);
        } else {
            $name = str_replace('TwentyFirst', '21st', $name);
            $name = str_replace('Thousand', '1000', $name);
        }

        return $name;
    }

    public static function str_putcsv($array, $delimiter = ',', $enclosure = '"', $terminator = "\n")
    {
        // First convert associative array to numeric indexed array

        foreach ($array as $key => $value) {
            $workArray[] = $value;
        }

        $returnString = '';                 // Initialize return string

        $arraySize = \count($workArray);     // Get size of array

        for ($i = 0; $i < $arraySize; ++$i) {
            // Nested array, process nest item

            if (\is_array($workArray[$i])) {
                $returnString .= self::str_putcsv($workArray[$i], $delimiter, $enclosure, $terminator);
            } else {
                switch (\gettype($workArray[$i])) {
                    // Manually set some strings

                    case 'NULL':     $_spFormat = '';
                        break;

                    case 'boolean':  $_spFormat = (true == $workArray[$i]) ? 'true' : 'false';
                        break;

                        // Make sure sprintf has a good datatype to work with

                    case 'integer':  $_spFormat = '%i';
                        break;

                    case 'double':   $_spFormat = '%0.2f';
                        break;

                    case 'string':   $_spFormat = '%s';
                        break;

                        // Unknown or invalid items for a csv - note: the datatype of array is already handled above, assuming the data is nested

                    case 'object':
                    case 'resource':
                    default:         $_spFormat = '';
                        break;
                }

                $returnString .= \sprintf('%2$s'.$_spFormat.'%2$s', $workArray[$i], $enclosure);

                $returnString .= ($i < ($arraySize - 1)) ? $delimiter : $terminator;
            }
        }

        // Done the workload, return the output information

        return $returnString;
    }
}
