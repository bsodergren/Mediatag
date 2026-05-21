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

$CreateDirs = [];

if (defined('CONFIG')) {
    define('__HOME__', CONFIG['HOME']);
    define('__PLEX_HOME__', CONFIG['PLEX_HOME']);
    define('__APP_HOME__', CONFIG['APP_HOME']);
    define('__WEB_HOME__', CONFIG['WEB_HOME']);
}

if (defined('__HOME__')) {
    define('__PORNHUB_DIR__', __HOME__ . '/pornhub_db');
    define('__DOWNLOAD_DIR__', __PORNHUB_DIR__ . '/download');
    define('__RAW_FILES_DIR__', __PORNHUB_DIR__ . '/raw');
    define('__FINISHED_FILES_DIR__', __PORNHUB_DIR__ . '/finished');
    define('__IMPORT_DIFF_DIR__', __PORNHUB_DIR__ . '/import');
    define('__JSON_DIR__', __PORNHUB_DIR__ . '/json');
    define('__CSV_DIR__', __PORNHUB_DIR__ . '/CSV');
    define('__NEW_CSV_DIR__', __CSV_DIR__ . '/new');
    define('__PREVIOUS_CSV_DIR__', __CSV_DIR__ . '/old');
    define('__FINISHED_CSV_DIR__', __CSV_DIR__ . '/done');
}

if (defined('__PLEX_HOME__')) {
    define('__PLEX_PL_DIR__', __PLEX_HOME__ . '/Playlists');
    define('__PLEX_PL_TMP_DIR__', __PLEX_PL_DIR__ . '/.tmp');
    define('__PLEX_PL_ID_DIR__', __PLEX_PL_DIR__ . '/ids');
    define('__PLEX_PL_LIST_DIR__', __PLEX_PL_DIR__ . '/lists');

    define('__PLEX_DOWNLOAD__', __PLEX_HOME__ . '/Downloads');
    define('__PLEX_DOWNLOADED__', __PLEX_HOME__ . '/Downloaded');

    define('__CACHE_DIR__', __PLEX_HOME__ . '/.cache');
    define('__JSON_CACHE_DIR__', __CACHE_DIR__ . '/json');
    define('__PH_CACHE_DIR__', __CACHE_DIR__ . '/Pornhub');
    define('__STUDIO_CACHE_DIR__', __CACHE_DIR__ . '/Studio');
    define('__STUDIO_JSON_CACHE_DIR__', __STUDIO_CACHE_DIR__ . '/json');
    define('__APP_CACHE_DIR__', __CACHE_DIR__ . '/mediatag');
    $CreateDirs[] = __PLEX_PL_ID_DIR__;
    $CreateDirs[] = __PLEX_PL_LIST_DIR__;

    $CreateDirs[] = __STUDIO_JSON_CACHE_DIR__;
    $CreateDirs[] = __PLEX_PL_DIR__;
    $CreateDirs[] = __JSON_CACHE_DIR__;
    $CreateDirs[] = __APP_CACHE_DIR__;
    $CreateDirs[] = __PLEX_DOWNLOAD__;
}

if (defined('__APP_HOME__')) {
    define('__PLEX_VAR_DIR__', __APP_HOME__ . '/var');
    define('__PLEX_STUDIO_JSON_DIR__', __PLEX_VAR_DIR__ . '/json');
    define('__LOGFILE_DIR__', __PLEX_VAR_DIR__ . '/log');
    define('__DB_BACKUP_ROOT__', __PLEX_VAR_DIR__ . '/db');
    define('__PATTERNS_LIB_DIR__', __APP_HOME__ . '/app/Patterns');
    define('__COMMANDS_DIR__', __APP_HOME__ . '/app/Commands');

    $CreateDirs[] = __LOGFILE_DIR__;
}

if (defined('__WEB_HOME__')) {
    define('__INC_WEB_THUMB_ROOT__', __WEB_HOME__);
    define('__INC_WEB_CAPTION_ROOT__', __WEB_HOME__ . '/videos/captions');
    define('__INC_WEB_THUMB_URL__', '/images/plex/thumbnails');
    define('__INC_WEB_THUMB_DIR__', __INC_WEB_THUMB_ROOT__ . __INC_WEB_THUMB_URL__);
    define('__INC_WEB_CHAPTER_URL__', '/images/plex/chapterImages');
    define('__INC_WEB_CHAPTER_DIR__', __INC_WEB_THUMB_ROOT__ . __INC_WEB_CHAPTER_URL__);
    define('__INC_WEB_PREVIEW_URL__', '/images/plex/preivew');
    define('__INC_WEB_PREVIEW_DIR__', __INC_WEB_THUMB_ROOT__ . __INC_WEB_PREVIEW_URL__);

    $CreateDirs[] = __INC_WEB_THUMB_DIR__;
    $CreateDirs[] = __INC_WEB_CHAPTER_DIR__;
    $CreateDirs[] = __INC_WEB_PREVIEW_DIR__;
}
// define('__PH_USERNAME__', CONFIG['PH_USERNAME']);
// define('__PH_PASSWORD__', CONFIG['PH_PASSWORD']);

define('__DATA_LIB__', __CONFIG_LIB__ . '/data');
define('__DATA_LISTS__', __CONFIG_LIB__ . '/data/list');
define('__DATA_MAPS__', __CONFIG_LIB__ . '/data/map');
define('__DATA_TEMPLATES__', __CONFIG_LIB__ . '/data/template');

define('__CREATE_DIRS__', $CreateDirs);

// dd(__INC_WEB_THUMB_DIR__);
