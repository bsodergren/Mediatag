<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Core;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UTM\Bundle\Stash\Cache;
use UTM\Utilities\Option;

class MediaCache
{
    public static $stash;

    public $input;

    public $output;

    public static function init(InputInterface $input = null, OutputInterface $output = null)
    {
        // utminfo();

        Option::init($input);
        if (! \defined('__LIBRARY__')) {
            \define('__LIBRARY__', 'tmp');
        }
        if (! is_dir(__APP_CACHE_DIR__ . '/' . __LIBRARY__)) {
            mkdir(__APP_CACHE_DIR__ . '/' . __LIBRARY__, 0777, true);
        }

        self::$stash = Cache::file(function (): void {
            $this->setCacheDir(__APP_CACHE_DIR__ . '/' . __LIBRARY__);

        });

        if (true == Option::isTrue('flush')) {
            self::$stash->flush();
            exit('cache flushed');
        }
    }

    public static function get($key)
    {
        // utminfo(func_get_args());

        if (true == Option::isTrue('nocache')) {
            return false;
        }

        return self::$stash->get($key);
    }

    public static function put($key, $value)
    {
        // utminfo(func_get_args());

        // self::forget($key);

        return self::$stash->put($key, $value);
    }

    public static function flush()
    {
        // utminfo(func_get_args());

        // self::$stash->flush();

        // exit('cache flushed');
    }

    public static function forget($key)
    {
        // utminfo(func_get_args());

        self::$stash->forget($key);
    }
}
