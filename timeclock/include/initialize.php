<?php
defined('DS')            ? null : define('DS', DIRECTORY_SEPARATOR);
defined('LIB_PATH')      ? null : define('LIB_PATH', __DIR__);
defined('SITE_ROOT')     ? null : define('SITE_ROOT',     substr(LIB_PATH,  0, strrpos(LIB_PATH,  DS)));
defined('SERVER_ROOT')   ? null : define('SERVER_ROOT',   substr(SITE_ROOT, 0, strrpos(SITE_ROOT, DS)));
defined('VIEWS')		 ? null : define('VIEWS',         SITE_ROOT . DS . "views");
defined('PUBLIC_ROOT')	 ? null : define('PUBLIC_ROOT',   SITE_ROOT . DS . "public");

require_once(LIB_PATH.DS.'sessions.php');
require_once(LIB_PATH.DS.'functions.php');
require_once(LIB_PATH.DS.'MySQLiDatabase.php');
//require_once(LIB_PATH.DS.'user.php');
require_once(LIB_PATH.DS.'timeclock.php');
require_once(LIB_PATH.DS.'pdf.php');
?>