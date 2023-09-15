<?php
defined('DS')          ? null : define('DS', DIRECTORY_SEPARATOR);
defined('LIB_PATH')    ? null : define('LIB_PATH', __DIR__);
defined('SITE_ROOT')   ? null : define('SITE_ROOT',     substr(LIB_PATH,  0, strrpos(LIB_PATH,  DS)));
defined('SERVER_ROOT') ? null : define('SERVER_ROOT',   substr(SITE_ROOT, 0, strrpos(SITE_ROOT, DS)));
defined('VIEWS')       ? null : define('VIEWS',         SITE_ROOT . DS . "views");
defined('PUBLIC_ROOT') ? null : define('PUBLIC_ROOT',   SITE_ROOT . DS . "public");
defined('RED_STAR')    ? null : define('STAR',         '<span style="color:red;">*</span>');

require_once(LIB_PATH.DS.'sessions.php');
require_once(LIB_PATH.DS.'functions.php');
require_once(LIB_PATH.DS.'common.php');
require_once(LIB_PATH.DS.'database.php');
//require_once(LIB_PATH.DS.'user.php');
require_once(LIB_PATH.DS.'timeclock.php');
require_once(LIB_PATH.DS.'pdf.php');

$h = explode("/",$_SERVER['PHP_SELF']);
if($h[1] == 'timeclock' && $h[2] == 'public'){
    $host_public = "http://localhost/timeclock/public";
}else{
	$host_public = 'http://' . $_SERVER['HTTP_HOST'];
}
//echo "\$host_public: $host_public <br>";
//die();
