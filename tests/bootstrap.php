<?php
/**
 * Shared PHPUnit bootstrap for BOTH suites (Unit and Integration).
 *
 * It only ever defines path/framework constants and loads class
 * declarations. It never opens a database connection itself — Database.php
 * no longer has a side-effecting instantiation at the bottom of the file
 * (that bootstrap now lives in include/initialize.php, which tests don't
 * load), so simply requiring these files is safe for the Unit suite.
 *
 * DB_* constants are dummy placeholders here. The Integration suite
 * overrides them with real values before requiring this file — see
 * tests/bootstrap-integration.php.
 */

define('DS',          DIRECTORY_SEPARATOR);
define('SITE_ROOT',   dirname(__DIR__));
define('PUBLIC_ROOT', SITE_ROOT . DS . 'public');
define('LIB_PATH',    SITE_ROOT . DS . 'include');

if (!defined('DB_HOST')) define('DB_HOST', 'unit-tests-should-never-connect.invalid');
if (!defined('DB_USER')) define('DB_USER', 'test');
if (!defined('DB_PASS')) define('DB_PASS', 'test');
if (!defined('DB_NAME')) define('DB_NAME', 'test');
if (!defined('APP_ENV')) define('APP_ENV', 'testing');

require_once LIB_PATH . DS . 'Functions.php';
require_once LIB_PATH . DS . 'EnvConstants.php';
require_once LIB_PATH . DS . 'Common.php';
require_once LIB_PATH . DS . 'Database.php';
require_once LIB_PATH . DS . 'Timeclock.php';
require_once LIB_PATH . DS . 'Utilities.php';

/**
 * Build a Timeclock (or any Database subclass) instance WITHOUT running its
 * constructor chain, so pure-logic methods can be exercised without a live
 * database connection. Timeclock's constructor calls parent::__construct()
 * (Database), which opens a real mysqli connection and queries
 * INFORMATION_SCHEMA — that is by design (Timeclock IS-A Database in this
 * codebase) and is exactly why DB-touching methods belong in the
 * Integration suite instead.
 *
 * Typed properties normally set inside the skipped constructor are
 * initialized here to the same defaults the constructor would have used,
 * so methods that read them (center(), justify(), pageHeaderPrep()) don't
 * hit "must not be accessed before initialization" errors.
 */
function makeUnconstructedTimeclock(): Timeclock
{
    $reflection = new ReflectionClass(Timeclock::class);
    /** @var Timeclock $tc */
    $tc = $reflection->newInstanceWithoutConstructor();

    $set = function (string $prop, $value) use ($reflection, $tc) {
        $p = $reflection->getProperty($prop);
        $p->setAccessible(true);
        $p->setValue($tc, $value);
    };

    // Mirrors Timeclock::__construct() with the default $padding = 0.
    $set('margin', str_repeat(' ', $tc->padding));
    $set('left_right_margin', 2 * $tc->padding);
    $set('limit', $tc->column_count - $tc->padding - 6);
    $set('date_and_2_spaces', str_repeat(' ', 10));
    $set('eol', "\\line" . PHP_EOL);
    $set('ff', "\\page" . "\\line" . PHP_EOL);

    return $tc;
}
