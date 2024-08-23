<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;


use UTM\Bundle\Monolog\UTMLog;
use UTM\Utilities\Debug\Timer;
use Nette\Utils\Arrays;
use Symfony\Component\Filesystem\Filesystem;

trait MediaLibrary
{
    public function getLibrary($exit = true): void
    {
        utminfo([Mediatag::$index++=>[__FILE__,__LINE__,__METHOD__]]);

        $curent_dir   = getcwd();
        UTMLog::logger('Current Directory', $curent_dir);
        if (false === $exit) {
            return;
        }
        $in_directory = (new Filesystem())->makePathRelative($curent_dir, __PLEX_HOME__);

        $success      = preg_match('/([^\/]*)\/([^\/]+)?/', $in_directory, $match);

        Timer::watch('GetLibrary');
        if (0 == \count($match)) {
            self::$Console->writeLn('your in a wrong spot ' . $curent_dir, 'error');

            UTMLog::logger('Wrong spot?', $curent_dir);
            if (true === $exit) {
                exit;
            }

            if (!\defined('__LIBRARY__')) {
                \define('__LIBRARY__', 'temp');
            }
        } else {
            if (!Arrays::contains(__LIBRARIES__, $match[1])) {
                UTMLog::logger('Not in a Library directory?', $curent_dir);

                self::$Console->writeLn('your in a wrong spot ' . $curent_dir, 'error');
                if (true === $exit) {
                    exit;
                }
            } else {
                if (!\defined('__LIBRARY__')) {
                    \define('__LIBRARY__', $match[1]);
                }

                UTMLog::logger('In Directory', __LIBRARY__);
            }
        }
    }
}
