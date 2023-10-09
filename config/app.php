<?php 

define('ENV', parse_ini_file(realpath(dirname(__FILE__, 2) . '/env.ini')));

$app = new \GTG\MVC\Application(dirname(__DIR__), [
    'projectUrl' => ENV['app_url'],
    'session' => require_once __DIR__ . '/session.php',
    'db' => require_once __DIR__ . '/database.php',
    'smtp' => require_once __DIR__ . '/smtp.php',
    'view' => require_once __DIR__ . '/view.php',
    'errorHandler' => require_once __DIR__ . '/error-handler.php',
    'data' => require_once __DIR__ . '/app-data.php'
]);

if($app->errorHandler) {
    GTG\MVC\Exceptions\ErrorHandler::setErrorUrl($app->errorHandler['url']);
}

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
ini_set('ignore_repeated_source', true);
ini_set('log_errors', true);
error_reporting( E_ALL );

set_error_handler(array('GTG\MVC\Exceptions\ErrorHandler', 'control'), E_ALL);
register_shutdown_function(array('GTG\MVC\Exceptions\ErrorHandler', 'shutdown'));

require_once(realpath(dirname(__FILE__) . '/date-utils.php'));
require_once(realpath(dirname(__FILE__) . '/utils.php'));

setlocale(LC_ALL, $app->session->getLanguage()[1]);
putenv('LANGUAGE=' . $app->session->getLanguage()[1]);

bindtextdomain('messages', dirname(__FILE__, 2) . '/lang');
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

date_default_timezone_set('America/Recife');

return $app;