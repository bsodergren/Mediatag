<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use Mediatag\Core\Mediatag;


use UTM\Bundle\Monolog\UTMLog;

trait Studio
{
    /**
     * mapStudio.
     */
    public function mapStudio($studio)
    {
        utminfo();

        $key = strtolower($studio);
        if (\array_key_exists($key, STUDIO_MAP)) {
            return STUDIO_MAP[$key];
        }

        return $studio;
    }

    /**
     * getStudioRegex.
     */
    public function getStudioRegex()
    {
        utminfo();

        return $this->getKeyValue('studio', 'pattern');
    }

    /**
     * getStudio.
     */
    public function getStudio()
    {
        utminfo();

        // UTMlog::Logger('Studio Key', $this->video_name);
        //  utmdd([__METHOD__,$this->getStudioRegex()]);
        if (true == $this->getStudioRegex()) {
            $return = preg_replace_callback($this->getStudioRegex(), function ($matches) {
                if (\array_key_exists($matches[1], $this->replace_studios)) {
                    return $this->replace_studios[$matches[1]];
                }

                return null;
            }, $this->video_name);
            if ($return == $this->video_name) {
                return false;
            }
            // UTMlog::Logger('file studio', $return);

            return ucwords($return);
        }

        return false;
    }

    public static function customStudio($key_studio, $arr)
    {
        utminfo();

        if (false == self::$StudioKey) {
            self::$StudioKey = $key_studio;
        }

        if (\array_key_exists(1, $arr)) {
            if (self::$StudioKey != $arr[0]) {
                if ($key_studio == $arr[1]) {
                    $tmp    = $arr[0];
                    $arr[0] = $arr[1];
                    $arr[1] = $tmp;
                }
            }
        }

        return $arr;
    }
}
