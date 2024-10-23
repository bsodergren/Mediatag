<?php
/**
 * Command like Metatag writer for video files.
 */

define('__SCRIPT_NAME__', basename($_SERVER['SCRIPT_NAME'], '.php'));

function DEFINE_DIR($definition, $value)
{
    if (! is_dir($value)) {
        mkdir($value, 0777, true);
    }
    define($definition, $value);
}

define('__HOME__', CONFIG['HOME']);
define('__PLEX_HOME__', CONFIG['PLEX_HOME']);
define('__APP_HOME__', CONFIG['APP_HOME']);
define('__WEB_HOME__', CONFIG['WEB_HOME']);
// define('__PH_USERNAME__', CONFIG['PH_USERNAME']);
// define('__PH_PASSWORD__', CONFIG['PH_PASSWORD']);

const __DATA_LIB__            = __CONFIG_LIB__ . '/data';
const __DB_BACKUP_ROOT__      = __PROJECT_ROOT__ . '/db';

const __PLEX_VAR_DIR__          = __APP_HOME__ . '/var';
const __LOGFILE_DIR__         = __PLEX_VAR_DIR__ . '/log';

const __PLEX_PL_DIR__         = __PLEX_HOME__ . '/Playlists';
const __PLEX_PL_TMP_DIR__     = __PLEX_PL_DIR__ . '/.tmp';
const __PLEX_PL_ID_DIR__      = __PLEX_PL_DIR__ . '/ids';
const __PLEX_PL_LIST_DIR__    = __PLEX_PL_DIR__ . '/lists';


const __PLEX_DOWNLOAD__       = __PLEX_HOME__ . '/Downloads';
const __CACHE_DIR__           = __PLEX_HOME__ . '/.cache';
const __JSON_CACHE_DIR__      = __CACHE_DIR__ . '/json';
const __PH_CACHE_DIR__        = __CACHE_DIR__ . '/Pornhub';

const __APP_CACHE_DIR__       = __PLEX_VAR_DIR__ . '/cache';

const __PATTERNS_LIB_DIR__    = __APP_HOME__ . '/app/Patterns';
const __INC_WEB_THUMB_ROOT__  = __WEB_HOME__;

const __INC_WEB_THUMB_URL__   = '/images/plex/thumbnails';
const __INC_WEB_THUMB_DIR__   = __INC_WEB_THUMB_ROOT__ . __INC_WEB_THUMB_URL__;

const __INC_WEB_CHAPTER_URL__ = '/images/plex/chapterImages';
const __INC_WEB_CHAPTER_DIR__ = __INC_WEB_THUMB_ROOT__ . __INC_WEB_CHAPTER_URL__;


const __INC_WEB_PREVIEW_URL__ = '/images/plex/preview';
const __INC_WEB_PREVIEW_DIR__ = __INC_WEB_THUMB_ROOT__ . __INC_WEB_PREVIEW_URL__;


const __DATA_LISTS__          = __CONFIG_LIB__ . '/data/list';
const __DATA_MAPS__           = __CONFIG_LIB__ . '/data/map';
const __DATA_TEMPLATES__      = __CONFIG_LIB__ . '/data/template';

const __PORNHUB_DIR__         = __HOME__ . '/pornhub_db';
const __DOWNLOAD_DIR__        = __PORNHUB_DIR__ . '/download';
const __RAW_FILES_DIR__       = __PORNHUB_DIR__ . '/raw';
const __FINISHED_FILES_DIR__  = __PORNHUB_DIR__ . '/finished';
const __IMPORT_DIFF_DIR__     = __PORNHUB_DIR__ . '/import';
const __JSON_DIR__            = __PORNHUB_DIR__ . '/json';

const __CSV_DIR__             = __PORNHUB_DIR__ . '/CSV';

const __NEW_CSV_DIR__         = __CSV_DIR__ . '/new';
const __PREVIOUS_CSV_DIR__    = __CSV_DIR__ . '/old';
const __FINISHED_CSV_DIR__    = __CSV_DIR__ . '/done';

define(
    '__CREATE_DIRS__',
    [__JSON_CACHE_DIR__,
        __PLEX_PL_DIR__,
        __PLEX_DOWNLOAD__,
        __PLEX_PL_ID_DIR__,
        __PLEX_PL_LIST_DIR__,
        __INC_WEB_THUMB_DIR__,
        __INC_WEB_CHAPTER_DIR__,
        __INC_WEB_PREVIEW_DIR__,


    ],
);


// dd(__INC_WEB_THUMB_DIR__);
