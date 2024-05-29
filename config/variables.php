<?php
/**
 * Command like Metatag writer for video files.
 */

$dbConfig = __CONFIG_LIB__.\DIRECTORY_SEPARATOR.'database.yaml';

UTM\Utilities\Loader::loadDatabase($dbConfig, "__MYSQL_", "__", "__");

// dd(get_defined_constants(true)['user']);

// define('__MEDIATAG_PREFIX__', 'mediatag_');
// define('__MYSQL_VIDEO_FILE__', __MEDIATAG_PREFIX__.'video_file');
// define('__MYSQL_VIDEO_METADATA__', __MEDIATAG_PREFIX__.'video_metadata');
// define('__MYSQL_VIDEO_INFO__', __MEDIATAG_PREFIX__.'video_info');
// define('__MYSQL_VIDEO_CUSTOM__', __MEDIATAG_PREFIX__.'video_custom');
// define('__MYSQL_TAGS__', __MEDIATAG_PREFIX__.'tags');

// define('__MYSQL_ARTISTS__', __MEDIATAG_PREFIX__.'artists');
// define('__MYSQL_STUDIOS__', __MEDIATAG_PREFIX__.'studios');
// define('__MYSQL_GENRE__', __MEDIATAG_PREFIX__.'genre');
// define('__MYSQL_KEYWORD__', __MEDIATAG_PREFIX__.'keyword');
// define('__MYSQL_TITLE__', __MEDIATAG_PREFIX__.'title');

// define('__PLEXWEB_PREFIX__', 'plexweb_');
// define('__MYSQL_PLAYLIST_DATA__', __PLEXWEB_PREFIX__.'playlist_data');
// define('__MYSQL_PLAYLIST_VIDEOS__', __PLEXWEB_PREFIX__.'playlist_videos');
// define('__MYSQL_VIDEO_SEQUENCE__', __PLEXWEB_PREFIX__.'video_sequence');
// define('__MYSQL_WORDMAP__', __PLEXWEB_PREFIX__.'wordMap');
// define('__MYSQL_SETTINGS__', __PLEXWEB_PREFIX__.'settings');
// define('__MYSQL_SEARCH_DATA__', __PLEXWEB_PREFIX__.'search_data');
// define('__MYSQL_VIDEO_CHAPTER__', __PLEXWEB_PREFIX__.'video_chapter');
// define('__MYSQL_SEQUENCE_FUNC_TABLE__', 'sequence');

define(
    '__MYSQL_TRUNC_TABLES__',
    [
        __MYSQL_VIDEO_FILE__,
        __MYSQL_VIDEO_METADATA__,
        __MYSQL_VIDEO_INFO__,
        __MYSQL_VIDEO_CUSTOM__,
        __MYSQL_PLAYLIST_VIDEOS__,
        __MYSQL_PLAYLIST_DATA__,
    ]
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
    ]
);

define(
    '__LIBRARIES__',
    [
        'Videos',
         'BiSexual',
        'Pornhub',
        'Studio',
        'Studios',
        'Home',
        'Downloads',
        'Playlists',
    ]
);

define(
    '__SKIP_STUDIOS__',
    [
       'New',
        'Models',
        'Amateur',
        'Animation',
        'Misc',
        'Downloads',
        'Favorite',
        'Premium',
        'Pornhub',
        'fav',
        'OnlyFans',

        'Sort',
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
    ]
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
        'Animation',
      //  'Hot',
      //  'Clips',
      //  'misc',
    ]
);

$genre_regex_string = strtolower(implode("|", __GENRE_LIST__));
define(
    '__GENRE_REGEX__',
    '/[a-zA-Z _0-9\.\/]*\/('.$genre_regex_string.')(.*)?(\/*.mp4)?/i'
);
// '/[a-zA-Z _0-9\.\/]*\/(group|mmf|mff|single|only girls|bimale|trans|only blowjobs|compilation)(.*)?(\/*.mp4)?/i'

// $console_width = (int) @exec('tput cols');
// if($console_width == 0) {
$console_width = 120;
// }

define('__CONSOLE_WIDTH__', $console_width);

//define('__CONSOLE_WIDTH__', 900);

define('__LIMIT_CRON_QUERY__', 1000);

define('__CACHE_TIMEOUT__', 240);

define('__TIMER_LOG__', true);
define('__TIMER_DISPLAY__', false);

const PHP_DBL = \PHP_EOL.\PHP_EOL;
const PHP_TAB = "\t";
