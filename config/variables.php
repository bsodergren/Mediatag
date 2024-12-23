<?php
/**
 * Command like Metatag writer for video files.
 */

$dbConfig = __CONFIG_LIB__.\DIRECTORY_SEPARATOR.'database.yaml';

UTM\Utilities\Loader::loadDatabase($dbConfig, '__MYSQL_', '__', '__');

define(
    '__MYSQL_TRUNC_TABLES__',
    [
        __MYSQL_VIDEO_FILE__,
        __MYSQL_VIDEO_METADATA__,
        __MYSQL_VIDEO_INFO__,
        __MYSQL_VIDEO_CUSTOM__,
        __MYSQL_PLAYLIST_VIDEOS__,
        __MYSQL_PLAYLIST_DATA__,
    ],
);

// define('__PH_DB_TABLE__', 'videos33');

define('__MAX_SQL_ITEMS__', 5000);

define(
    '__CHANNELS__',
    [
        'Studios',
        'OnlyFans',
        'Animation',
        'Models',
        'Amateur',
        'Channels',
        'New',
        'Premium',
        'Sort',
        'Misc',
    ],
);

define(
    '__LIBRARIES__',
    [
        'Videos',
        'BiSexual',
        'Pornhub',
        'Studio',
        'Studios',
        'HomeVideos',
        'Downloads',
        'Playlists',
        'Home Pictures',
    ],
);

define(
    '__SKIP_STUDIOS__',
    [
        'New',
        'Models',
        'Amateur',
        'Animation',
        // 'Misc',
        'Downloads',
        'Favorite',
        'Premium',
        'Pornhub',
        'fav',
        'OnlyFans',

        // 'Sort',
        'Channels',
        'Maybe',
        'Amateur Models',
        'Studios',
        // 'group',
        // 'mmf',
        // 'mff',
        // 'single',
        // 'only girls',
        // 'bimale',
        // 'trans',
        // 'blowjob',

        // 'only blowjobs',
        // 'compilation',
        // 'Bisexual',
    ],
);

define(
    '__GENRE_LIST__',
    [
        'Double Penetration',
        'group',
        'mmf',
        'mff',
        'single',
        'only girls',
        'bimale',
        'trans',
        'blowjob',
        'only blowjobs',
        'compilation',
        'bisexual',
        'white',
        'Animation',
        'feature',
        'sort',
        'other',
        'Hotwife',
        //  'Hot',
        //  'Clips',
        //  'misc',
    ],
);

$genre_regex_string = strtolower(implode('|', __GENRE_LIST__));
define(
    '__GENRE_REGEX__',
    '/[a-zA-Z _0-9\.\/]*\/('.$genre_regex_string.')(.*)?(\/*.mp4)?/i',
);
// '/[a-zA-Z _0-9\.\/]*\/(group|mmf|mff|single|only girls|bimale|trans|only blowjobs|compilation)(.*)?(\/*.mp4)?/i'

// $console_width = (int) @exec('tput cols');
// if($console_width == 0) {
$console_width = 120;
// }

define('__CONSOLE_WIDTH__', $console_width);

// define('__CONSOLE_WIDTH__', 900);

define('__LIMIT_CRON_QUERY__', 1000);

define('__CACHE_TIMEOUT__', 240);

define('__TIMER_LOG__', true);
define('__TIMER_DISPLAY__', false);

const PHP_DBL = \PHP_EOL.\PHP_EOL;
const PHP_TAB = "\t";
