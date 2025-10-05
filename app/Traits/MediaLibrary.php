<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use const DIRECTORY_SEPARATOR;

use Mediatag\Core\Mediatag;
use Nette\Utils\Arrays;
use Symfony\Component\Filesystem\Filesystem;
use UTM\Bundle\Monolog\UTMLog;

use function count;
use function define;
use function defined;

trait MediaLibrary
{
    public function getLibrary($exit = true): void
    {
        // utminfo(func_get_args());

        $curent_dir = getcwd();
        // UTMlog::logger('Current Directory', $curent_dir);
        if ($exit === false) {
            if (! defined('__LIBRARY__')) {
                define('__LIBRARY__', false);
            }

            return;
        }
        $in_directory = (new Filesystem)->makePathRelative($curent_dir, __PLEX_HOME__);

        $success = preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);

        if (count($match) == 0) {
            Mediatag::$Console->writeLn('your in a wrong spot ' . $curent_dir, 'error');

            // UTMlog::logger('Wrong spot?', $curent_dir);
            if ($exit === true) {
                exit;
            }

            if (! defined('__LIBRARY__')) {
                define('__LIBRARY__', 'temp');
            }
        } else {
            if (! Arrays::contains(__LIBRARIES__, $match[1])) {
                // UTMlog::logger('Not in a Library directory?', $curent_dir);

                self::$Console->writeLn('your in a wrong spot ' . $curent_dir, 'error');
                if ($exit === true) {
                    exit;
                }
            } else {
                if (! defined('__LIBRARY__')) {
                    define('__LIBRARY__', $match[1]);
                }

                // UTMlog::logger('In Directory', __LIBRARY__);
            }
        }
        if (! defined('__LIBRARY_HOME__')) {
            define('__LIBRARY_HOME__', __PLEX_HOME__ . DIRECTORY_SEPARATOR . __LIBRARY__);
        }
    }
}
