<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits\Patterns;

use UTM\Bundle\Monolog\UTMLog;

use function array_key_exists;

trait Studio
{
    /**
     * mapStudio.
     */
    public function mapStudio($studio)
    {
        // utminfo(func_get_args());
        $key = strtolower($studio);
        if (array_key_exists($key, STUDIO_MAP)) {
            return STUDIO_MAP[$key];
        }

        return $studio;
    }

    /**
     * getStudioRegex.
     */
    public function getStudioRegex()
    {
        // utminfo(func_get_args());

        return $this->getKeyValue('studio', 'pattern');
    }

    /**
     * getStudio.
     */
    public function metaStudio()
    {
        // utminfo(func_get_args());

        // UTMlog::Logger('Studio Key', $this->video_name);
        if (false !== $this->getStudioRegex()) {
            $return = preg_replace_callback($this->getStudioRegex(), function ($matches) {
                $studioKey = str_replace('-', '', $matches[1]);
                if (array_key_exists($studioKey, $this->replace_studios)) {
                    return $this->replace_studios[$studioKey];
                }

                return null;
            }, $this->video_name);
            if ($return == $this->video_name) {
                return null;
            }

            return ucwords($return);
        }

        return null;
    }

    public static function customStudio($key_studio, $arr)
    {
        // utminfo(func_get_args());

        if (false == self::$StudioKey) {
            self::$StudioKey = $key_studio;
        }

        if (array_key_exists(1, $arr)) {
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
