<?php
/***
 * Database Applications for the Web in No-time - Dawn
 ***
 * This file sets all constants used by the framework. They are stored in a
 * separate file (not in 'dawn.php') to make it easy to run unit tests on the
 * components in the framework.
 *
 * Most of the constants in this file are set only if they weren't set before,
 * meaning that the main application file can override the default values used
 * below. Normally, however, this should hardly be necessary.
 ****
 * IMPORTANT:
 *   All constants that point to directories, MUST end with a directory
 *   delimiter. For example:
 *     define('APP_CACHE' , '/tmp/appname/');      // Unix
 *     define('APP_CONFIG', 'C:/Appname/Config/'); // Windows
 ***/

/***
 * Store the current time. This is more or less the time the application starts,
 * give or take a few microseconds.
 **/
define('DAWN_TIME_BEGIN', array_sum(explode(' ', microtime())));

/***
 * The constant APP_URL must be set, so bail out immediately if it wasn't.
 ***/
if (!defined('APP_URL')) exit('The constant APP_URL must be set!');

/***
 * Set the local root of the application framework.
 ***/
if (!defined('DAWN_ROOT')) define('DAWN_ROOT', current(pathinfo(__FILE__)) . '/');

/***
 * Dawn runs on top of Eclipse 3.x. If this library is globally available, this
 * constant may be defined as the empty string ('').
 ***/
if (!defined('ECLIPSE_ROOT')) define('ECLIPSE_ROOT', DAWN_ROOT . '../eclipse-3_3/');

/***
 * Dawn uses one configuration file as the main config file. This file may
 * include other configuration files. By default the main configuration file is
 * expected to be called 'main.cfg', but this can be changed if necessary.
 ***/
if (!defined('MAIN_CONFIG')) define('MAIN_CONFIG', 'main.cfg');

/***
 * The generated HTML is nicely formatted by using proper indentation. Blocks
 * are indented repeatedly - depending on their level - with some indentation
 * string. It defaults to two spaces.
 ***/
if (!defined('INDENT_STRING')) define('INDENT_STRING', '  ');

/***
 * Set the APP_CONFIG, APP_CACHE, APP_DEBUG, APP_RESET and APP_LOG
 * constants to reasonable values if they weren't set.
 ***/
if (!defined('APP_CONFIG')) define('APP_CONFIG', 'config/');
if (!defined('APP_CACHE'))  define('APP_CACHE' , 'cache/');
if (!defined('APP_DEBUG'))  define('APP_DEBUG' , false);
if (!defined('APP_RESET'))  define('APP_RESET' , true);
if (!defined('APP_LOG'))    define('APP_LOG'   , false);

/***
 * Set all non-defined application-specific constants to the empty string so
 * that all of them have a value Dawn can work with.
 ***/
if (!defined('APP_COMMANDS'))     define('APP_COMMANDS'    , '');
if (!defined('APP_FIELDS'))       define('APP_FIELDS'      , '');
if (!defined('APP_FORMS'))        define('APP_FORMS'       , '');
if (!defined('APP_LAYOUTS'))      define('APP_LAYOUTS'     , '');
if (!defined('APP_LOCALES'))      define('APP_LOCALES'     , '');
if (!defined('APP_TRANSLATIONS')) define('APP_TRANSLATIONS', '');
if (!defined('APP_USERS'))        define('APP_USERS'       , '');
if (!defined('APP_WIDGETS'))      define('APP_WIDGETS'     , '');

/***
 * Set the constants for the Dawn-specific directories; normally these
 * shouldn't have to be modified at all.
 ***/
define('DAWN_SYSTEM'      , DAWN_ROOT . 'system/');
define('DAWN_COMMANDS'    , DAWN_ROOT . 'commands/');
define('DAWN_ERRORS'      , DAWN_ROOT . 'errors/');
define('DAWN_FIELDS'      , DAWN_ROOT . 'fields/');
define('DAWN_FORMS'       , DAWN_ROOT . 'forms/');
define('DAWN_LAYOUTS'     , DAWN_ROOT . 'layouts/');
define('DAWN_LOCALES'     , DAWN_ROOT . 'locales/');
define('DAWN_TRANSLATIONS', DAWN_ROOT . 'translations/');
define('DAWN_USERS'       , DAWN_ROOT . 'users/');
define('DAWN_WIDGETS'     , DAWN_ROOT . 'widgets/');

/***
 * Finally, here are two constants that should not be changed by anyone but me:
 * Dawn's version number and the location of Dawn's homepage on the Internet.
 * Please be so kind to keep these at their default values.
 ***/
define('DAWN_VERSION', '1.1');
define('DAWN_URL'    , 'http://www.sunlight.tmfweb.nl/dawn/');
?>
