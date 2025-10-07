<?php

/**
 * Command like Metatag writer for video files.
 */

use Camoo\Config\Config;
use Mediatag\Core\EnvLoader;
use Mediatag\Core\MediaLogger;
use Slim\Factory\AppFactory;
use UTM\Utilities\Debug\Debug;
use UTM\Utilities\Debug\UtmStopWatch;
use UTM\Utm;

// die(get_include_path());

define('__PROJECT_ROOT__', __ROOT_DIRECTORY__);
define('__COMPOSER_LIB__', __ROOT_DIRECTORY__ . '/vendor');

set_include_path(get_include_path() . \PATH_SEPARATOR . __COMPOSER_LIB__);

require_once __COMPOSER_LIB__ . '/autoload.php';

Utm::loadConifg(__ROOT_DIRECTORY__ . \DIRECTORY_SEPARATOR . 'config.ini');
Utm::LoadEnv(__ROOT_DIRECTORY__)->load();

define('CONFIG', Utm::$UTM_CONFIG['path']);

new Utm;

$container = require __CONFIG_LIB__ . '/container.php';

define('__SQL_USER__', CONFIG['DB_USER']);
define('__SQL_PASSWD__', CONFIG['DB_PASS']);
define('__MYSQL_DATABASE__', CONFIG['DB_DATABASE']);

require_once __CONFIG_LIB__ . '/path_constants.php';
ini_set('error_log', __LOGFILE_DIR__ . '/phperror.log');

require_once __CONFIG_LIB__ . '/variables.php';
require_once __CONFIG_LIB__ . '/ConsoleEventListeners.php';

MediaLogger::$USE_DEBUG = false;
MediaLogger::$pruneLogs = false;

// Debug::$AppRootDir  = __APP_HOME__.\DIRECTORY_SEPARATOR.'app';
// Debug::$AppTraceDir = __LOGFILE_DIR__;
// Debug::$PrettyLogs  = false;
// Debug::$RealTimeLog = false;

// if (file_exists(__LOGFILE_DIR__.'/phperror.log')) {
//     unlink(__LOGFILE_DIR__.'/phperror.log');
// }

// UtmStopWatch::$display  = false;
// UtmStopWatch::$writeNow = false;
// // define('__SCRIPT_NAME__', basename($_SERVER['SCRIPT_FILENAME'],'.php'));
// TimerStart();
// utminfo('---- START OF PAGE VIEW ' . __SCRIPT_NAME__);
// utmdebug('---- START OF PAGE VIEW ' . __SCRIPT_NAME__);

// register_shutdown_function('utmshutdown', ['write' => ['info'],
// 'write'                                        => 'debug']);

AppFactory::setContainer($container);

return AppFactory::create();
