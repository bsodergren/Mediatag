<?php
/**
 * Command like Metatag writer for video files.
 */

// $dbConfig = __CONFIG_LIB__.\DIRECTORY_SEPARATOR.'database.yaml';

// UTM\Utilities\Loader::loadDatabase($dbConfig, '__MYSQL_', '__', '__');



/**
 * @global string Db_PLEXWEB_PREFIX
 */
define("Db_MEDIATAG_PREFIX", "mediatag_");

/**
 * @global string Db_PLEXWEB_PREFIX
 */
define("Db_PLEXWEB_PREFIX", "plexweb_");


/**
 * @global string __MYSQL_VIDEO_FILE
 */
define("__MYSQL_VIDEO_FILE__", Db_MEDIATAG_PREFIX."video_file");
/**
 * @global string __MYSQL_VIDEO_INFO
 */
define("__MYSQL_VIDEO_INFO__", Db_MEDIATAG_PREFIX."video_info");
/**
 * @global string __MYSQL_VIDEO_CUSTOM
 */
define("__MYSQL_VIDEO_CUSTOM__", Db_MEDIATAG_PREFIX."video_custom");
/**
 * @global string __MYSQL_VIDEO_METADATA
 */
define("__MYSQL_VIDEO_METADATA__", Db_MEDIATAG_PREFIX."video_metadata");
/**
 * @global string __MYSQL_STUDIOS
 */
define("__MYSQL_STUDIOS__", Db_MEDIATAG_PREFIX."studios");
/**
 * @global string __MYSQL_GENRE
 */
define("__MYSQL_GENRE__", Db_MEDIATAG_PREFIX."genre");
/**
 * @global string __MYSQL_ARTISTS
 */
define("__MYSQL_ARTISTS__", Db_MEDIATAG_PREFIX."artists");
/**
 * @global string __MYSQL_TAGS
 */
define("__MYSQL_TAGS__", Db_MEDIATAG_PREFIX."tags");
/**
 * @global string __MYSQL_KEYWORD
 */
define("__MYSQL_KEYWORD__", Db_MEDIATAG_PREFIX."keyword");
/**
 * @global string __MYSQL_TITLE
 */
define("__MYSQL_TITLE__", Db_MEDIATAG_PREFIX."title");
/**
 * @global string __MYSQL_GALLERY
 */
define("__MYSQL_GALLERY__", Db_MEDIATAG_PREFIX."gallery");



/**
 * @global string __MYSQL_VIDEO_CHAPTER
 */
define("__MYSQL_VIDEO_CHAPTER__", Db_PLEXWEB_PREFIX."video_chapter");
/**
 * @global string __MYSQL_SETTINGS
 */
define("__MYSQL_SETTINGS__", Db_PLEXWEB_PREFIX."settings");
/**
 * @global string __MYSQL_SEARCH_DATA
 */
define("__MYSQL_SEARCH_DATA__", Db_PLEXWEB_PREFIX."search_data");
/**
 * @global string __MYSQL_VIDEO_SEQUENCE
 */
define("__MYSQL_VIDEO_SEQUENCE__", Db_PLEXWEB_PREFIX."video_sequence");
/**
 * @global string __MYSQL_PLAYLIST_VIDEOS
 */
define("__MYSQL_PLAYLIST_VIDEOS__", Db_PLEXWEB_PREFIX."playlist_videos");
/**
 * @global string __MYSQL_PLAYLIST_DATA
 */
define("__MYSQL_PLAYLIST_DATA__", Db_PLEXWEB_PREFIX."playlist_data");
/**
 * @global string __MYSQL_FAVORITE_VIDEOS
 */
define("__MYSQL_FAVORITE_VIDEOS__", Db_PLEXWEB_PREFIX."favorite_videos");
/**
 * @global string __MYSQL_SMARTLIST_DATA
 */
define("__MYSQL_SMARTLIST_DATA__", Db_PLEXWEB_PREFIX."smartlist_data");
/**
 * @global string __MYSQL_WORDMAP
 */
define("__MYSQL_WORDMAP__", Db_PLEXWEB_PREFIX."wordMap");





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

// define('__PH___MYSQL__', 'videos33');

define('__MAX_SQL_ITEMS__', 5000);

define(
    '__CHANNELS__',
    [
        'Studios',
        'Favorite',
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
        // 'Favorite',
        'Clips',
        // 'Home',
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
        
        'Group',
        'MMF',
        'MFF',
        'Single',
        'Only Girls',
        
        'Trans',
        'Blowjob',
        'Only Blowjobs',
        'Compilation',
        'Bisexual',
        'Feature',
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
