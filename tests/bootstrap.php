<?php
/**
 * PHPUnit bootstrap — defines constants and stubs out DB-dependent globals
 * so pure-logic classes can be loaded without a live database connection.
 */

// ── Path constants expected by the app ────────────────────────────────────────
define('DS',          DIRECTORY_SEPARATOR);
define('SITE_ROOT',   dirname(__DIR__));
define('PUBLIC_ROOT', SITE_ROOT . DS . 'public');
define('LIB_PATH',    SITE_ROOT . DS . 'include');

// ── DB constants (not used by the classes under test, but required by includes)
define('DB_SERVER', 'localhost');
define('DB_USER',   'test');
define('DB_PASS',   'test');
define('DB_NAME',   'test');

// ── Load only the files whose classes we are testing ─────────────────────────
// functions.php has no class definition — just functions
require_once SITE_ROOT . DS . 'include' . DS . 'Functions.php';

// Timeclock constructor never touches the DB; it only sets string properties.
// Methods that use $database do so inside their bodies, so as long as we only
// call the pure methods in tests we can safely load the class.
require_once SITE_ROOT . DS . 'include' . DS . 'Timeclock.php';
