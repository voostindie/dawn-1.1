<?php
/***
 * Database Applications for the Web in No-time - Dawn
 ***
 * This file must be included in the main page of the application (typically
 * 'index.php'), but only AFTER setting a number of constants. See 'sample.php'
 * for a basic main page, and 'constants.php' for a list of all available
 * settings.
 ***/

/***
 * Read the global settings (all constants).
 ***/
require_once(current(pathinfo(__FILE__)) . '/constants.php');

/***
 * Make sure magic quotes are disabled. Processing data correctly is hard
 * enough without them; having to wonder if they are enabled only makes it
 * more difficult.
 ***/
set_magic_quotes_runtime(0);

/***
 * Enable or disable assertions, depending on whether APP_DEBUG is true or not.
 ***/
assert_options(ASSERT_ACTIVE, APP_DEBUG);

/***
 * In debug mode, include the Debug module and initialize it.
 ***/
assert('require_once(DAWN_SYSTEM . \'Debug.php\')');
assert('Debug::initialize()');

/***
 * In debug mode, clear the cache completely.
 ***/
assert('require_once(DAWN_SYSTEM . \'Cache.php\')');
assert('Cache::clear()');

/***
 * Include the most important Dawn class of all, instantiate an object for it,
 * call 'run' on the object, and destroy it when done.
 ***/
require_once(DAWN_SYSTEM . 'Application.php');
$dawn =& new Application();
$dawn->run();
unset($dawn);

/***
 * Finally, tell the Debug module we're done, and that's it!
 ***/
assert('Debug::finish()');
exit(0);
?>
