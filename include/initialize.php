<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!defined('DS'))          define('DS', DIRECTORY_SEPARATOR);
if(!defined('LIB_PATH'))    define('LIB_PATH', __DIR__);
if(!defined('SITE_ROOT'))   define('SITE_ROOT',     substr(LIB_PATH,  0, strrpos(LIB_PATH,  DS)));
if(!defined('SERVER_ROOT')) define('SERVER_ROOT',   substr(SITE_ROOT, 0, strrpos(SITE_ROOT, DS)));
if(!defined('VIEWS'))       define('VIEWS',         SITE_ROOT . DS . "views");
if(!defined('PUBLIC_ROOT')) define('PUBLIC_ROOT',   SITE_ROOT . DS . "public");
if(!defined('RED_STAR'))    define('RED_STAR',     '<span style="color:red;">*</span>');

require_once(LIB_PATH.DS.'sessions.php');
require_once(LIB_PATH.DS.'functions.php');
require_once(LIB_PATH.DS.'common.php');
require_once(LIB_PATH.DS.'database.php');
//require_once(LIB_PATH.DS.'user.php');
require_once(LIB_PATH.DS.'timeclock.php');
require_once(LIB_PATH.DS.'pdf.php');

$h = explode("/",$_SERVER['PHP_SELF']);
if(isset($h[2]) && $h[2]=='public'){
    $host_public = "//localhost/" . $h[1] . "/public";
}else{
    $host_public = '//' . $_SERVER['HTTP_HOST'];
}

//echo "<pre>";
//echo "\$_SERVER: "; print_r($_SERVER);
//die();