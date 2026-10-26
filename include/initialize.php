<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!defined('DS'))          define('DS', DIRECTORY_SEPARATOR);
if(!defined('LIB_PATH'))    define('LIB_PATH', __DIR__);
if(!defined('SITE_ROOT'))   define('SITE_ROOT',     substr(LIB_PATH,  0, strrpos(LIB_PATH,  DS)));
if(!defined('SERVER_ROOT')) define('SERVER_ROOT',   substr(SITE_ROOT, 0, strrpos(SITE_ROOT, DS)));
if(!defined('VIEWS'))       define('VIEWS',         SITE_ROOT . DS . "views");
if(!defined('PUBLIC_ROOT')) define('PUBLIC_ROOT',   SITE_ROOT . DS . "public");
if(!defined('RED_STAR'))    define('RED_STAR',     '<span style="color:red;">*</span>');

// Load .env constants first so DB_HOST and APP_ENV are available.
// DB_HOST should be set directly in .env — no port-sniffing needed.
require_once('envconstants.php');
(new EnvConstants(SITE_ROOT.DS.".env"))->load();

defined("TIMEZONE")
    ? date_default_timezone_set(TIMEZONE)
    : date_default_timezone_set('America/New_York');

// Display errors only if APP_DEBUG is true.
$debug = (defined('APP_DEBUG') && APP_DEBUG) ? 1 : 0;
ini_set('display_errors', $debug);
ini_set('display_startup_errors', $debug);
ini_set('log_errors', $debug);
error_reporting(E_ALL);   // this works if display_errors or log_errors is set

$include_list = ['session.php', 'functions.php', 'common.php', 'database.php', 'timeclock.php', 'pdf.php'];
foreach($include_list as $file) {
    require_once(LIB_PATH.DS.$file);
}


/* not sure all pages need to open db */
$database = new Database();
$db =& $database;

$h = explode("/",$_SERVER['PHP_SELF']);
if(isset($h[2]) && $h[2]=='public'){
    $host_public = "//localhost/" . $h[1] . "/public";
}else{
    $host_public = '//' . $_SERVER['HTTP_HOST'];
}
/*
echo "<pre>";
echo "\$_SERVER: "; print_r($_SERVER);
die();
*/
