<?php
/**
 * Command like Metatag writer for video files.
 */

use Camoo\Config\Config;
use Mediatag\Core\EnvLoader;
use Slim\Factory\AppFactory;
use UTM\Bundle\Monolog\UTMLog;

define('__PROJECT_ROOT__', __ROOT_DIRECTORY__);
define('__COMPOSER_LIB__', __ROOT_DIRECTORY__.'/vendor');

set_include_path(get_include_path().\PATH_SEPARATOR.__COMPOSER_LIB__);

require_once __COMPOSER_LIB__.'/autoload.php';


$config = new Config(__ROOT_DIRECTORY__.\DIRECTORY_SEPARATOR.'config.ini');
define('CONFIG', $config['path']);

EnvLoader::LoadEnv(__ROOT_DIRECTORY__)->load();
(new \UTM\Utm());

$container = require __CONFIG_LIB__.'/container.php';


define('__SQL_USER__', CONFIG['DB_USER']);
define('__SQL_PASSWD__', CONFIG['DB_PASS']);
define('__MYSQL_DATABASE__', CONFIG['DB_DATABASE']);

require_once __CONFIG_LIB__.'/path_constants.php';

require_once __CONFIG_LIB__.'/variables.php';
$log = new UTMLog(__SCRIPT_NAME__);
UTMLog::$Logger = $log;

UTMLog::LogStart(__SCRIPT_NAME__);

AppFactory::setContainer($container);

return AppFactory::create();
