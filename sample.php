<?php
/***
 * Database Applications for the Web in No-time - Dawn
 ***
 * This is a sample Dawn invocation file. Use this as a starting point for new
 * applications. Copy this file to a directory on the web root as 'index.php'
 * and fill in the blanks.
 *
 * This file describes the settings required to make a Dawn application run.
 * For a more complete description of the settings, see 'constants.php', as well
 * as the documentation.
 ****
 * IMPORTANT:
 *   All constants that point to directories, MUST end with a directory
 *   delimiter. For example:
 *     define('APP_CACHE' , '/tmp/appname/');      // Unix
 *     define('APP_CONFIG', 'C:/Appname/Config/'); // Windows
 ***/

/***
 * Dawn requires Eclipse, version 3.x. By default it is expected to be in the
 * same directory Dawn was installed in. For example if Dawn was unpacked in
 * '/data/www/lib/dawn', Eclipse is expected to be in '/data/www/lib/eclipse'.
 * If Eclipse was unpacked in a different directory, the constant ECLIPSE_ROOT
 * must be set to that directory. Alternatively, if Eclipse is in the global
 * PHP include path, the constant may be set to the empty string ('').
 ***/
//define('ECLIPSE_ROOT', '/path/to/eclipse-3_x/');
//define('ECLIPSE_ROOT', '');

/***
 * The absolute URL of this application. This constant MUST be set, or the
 * application won't even run.
 ***/
define('APP_URL', 'http://your.domain.com/dawn_application/index.php');

/***
 * The location of the application-specific configuration files. Do NOT place
 * these files on the web root in a production environment! (Or, if you do,
 * be sure to protect it, e.g. with a '.htaccess' file for Apache.)
 ***/
//define('APP_CONFIG', 'config/');

/***
 * The location of the application-specific cache files. The directory must be
 * writable by the web server, or the application will not run. Do NOT place
 * these files on the web root in a production environment! Or protect it at
 * least.
 ***/
//define('APP_CACHE', 'cache/');

/***
 * The application can be run in debug mode by setting the following constant
 * to true. Doing that has three major consequences:
 * 1. Assertions are enabled. The application runs various statements inside
 *    assertions in debug mode to make sure the code runs correctly. This can
 *    be very useful when implementing components yourself. If debug mode is
 *    disabled, so are assertions, and statements inside assertions are skipped
 *    by the PHP interpreter.
 * 2. The cache can be reset at program startup; see below.
 * 3. A log file can be kept; see below.
 ***/
//define('APP_DEBUG', true);

/***
 * In debug mode, the cache is normally cleared at program startup. Set the
 * constant APP_RESET to false to disable this so that the cache will be kept,
 * even in debug mode.
 ***/
//define('APP_RESET', false);

/***
 * In debug mode, it is possible to create a log file that records the various
 * steps the system makes. This can be useful for tracking bugs and problems,
 * as well as for making sure the right methods are called in the right system
 * state. By default logging is disabled even in debug mode, because it's slow
 * and only component developers are typically interested in it.
 ***/
//define('APP_LOG', true);

/***
 * The location of the log that is kept when the application is in debug mode
 * and logging is enabled. The default is the file 'dawn.log' in the cache
 * directory. Generally, the cache directory is a good place to store the log,
 * as it is writable by the web server, and outside of the web server root.
 ***/
//define('APP_LOG_FILE', APP_CACHE . 'dawn.log');

/***
 * The maximum size of the log file. On application startup in debug mode, the
 * size of the log file is checked, and if it is larger than APP_LOG_MAX bytes,
 * it is cleared. The default is 1 MB. Setting APP_LOG_MAX to a value smaller
 * than 0 will ensure the log file is never cleared.
 ***/
//define('APP_LOG_MAX', 1048576);

/***
 * The severity level of messages shown in the log file. By default everything
 * is logged, but this can be changed to any combination of the following
 * options:
 *  1: system messages (startup, state changes, shutdown)
 *  2: notices
 *  4: warnings
 *  8: errors
 ****/
//define('APP_LOG_LEVEL', 1 | 4 | 8);

/***
 * If the application has components, locales or translations of its own, the
 * paths to them must be defined in the appropriate constants:
 ***/
//define('APP_COMMANDS'    , 'commands/');
//define('APP_FIELDS'      , 'fields/');
//define('APP_FORMS'       , 'forms/');
//define('APP_LAYOUTS'     , 'layouts/');
//define('APP_LOCALES'     , 'locales/');
//define('APP_TRANSLATIONS', 'translations/');
//define('APP_USERS'       , 'users/');
//define('APP_WIDGETS'     , 'widgets/');

/***
 * Now that everything has been set, include the main Dawn file.
 ***/
require_once('/path/to/dawn/dawn.php');
?>
