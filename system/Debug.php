<?php
/***
 * Define the various severity levels used for logging messages
 ****/
define('DEBUG_LEVEL_SYSTEM' , 1);
define('DEBUG_LEVEL_NOTICE' , 2);
define('DEBUG_LEVEL_WARNING', 4);
define('DEBUG_LEVEL_ERROR'  , 8);

/***
 * Define the various consecutive states the application will be in.
 ***/
define('DEBUG_STATE_BEGIN' ,  1);
define('DEBUG_STATE_CREATE',  2);
define('DEBUG_STATE_BUILD' ,  4);
define('DEBUG_STATE_SHOW'  ,  8);
define('DEBUG_STATE_END'   , 16);

/***
 * Define the name of the global variable storing the application state and set
 * that variable to the very first state.
 ***/
define('DAWN_STATE', 'DAWN_STATE');
$GLOBALS[DAWN_STATE] = DEBUG_STATE_BEGIN;

/***
 * Set default application-specific debugging constants if they weren't defined
 ***/
if (!defined('APP_LOG_FILE'))  define('APP_LOG_FILE' , APP_CACHE . 'dawn.log');
if (!defined('APP_LOG_MAX'))   define('APP_LOG_MAX'  , 1048576);
if (!defined('APP_LOG_LEVEL')) define('APP_LOG_LEVEL', 15);

/***
 * Class Debug logs messages to a central file and keeps track of the system
 * state.
 * <p>
 *   If the constants APP_DEBUG and APP_LOG are set to true, this class is made
 *   available to the system, making it possible to log messages to a central
 *   file, and to keep track of the state the system is in.
 * </p>
 * <p>
 *   Dawn is a fairly complex system, running in a number consecutive states. In
 *   each state the application programmer is allowed to use certain parts of
 *   the system, while other parts of the system shouldn't be used at all. It is
 *   pretty easy to use some parts in a system state they shouldn't be used.
 *   While this is not necessarily erroneous, it will almost always result in a
 *   severe degradation of system performance. To aid the programmer in using
 *   the right parts of the system in the right state, the system keeps track of
 *   the state it is in. If a part of the system is used in the wrong state of
 *   the system, a message is printed to the system log.
 * </p>
 * <p>
 *   Calls to <code>Debug::log</code> should always be placed inside an
 *   assertion, in a string, like this:
 * </p>
 * <pre>
 *   assert('Debug::log(\'Nuclear bomb impact imminent!\')');
 * </pre>
 * <p>
 *   If APP_DEBUG is set to false, assertions are automatically disabled, 
 *   and messages will not be logged even if APP_LOG is true.. Placing all
 *   assertion expressions inside strings as shown above makes sure they are
 *   only evalulated when assertions are actually enabled, decreasing the impact
 *   on performance when they are disabled.
 * </p>
 * <p>
 *   Debug messages are always stored in the file APP_LOG_FILE, which can be set
 *   by applications. If the APP_LOG_FILE constant isn't set, it defaults to 
 *   'APP_CACHE/dawn.log'.
 * </p>
 * <p>
 *   The constant APP_LOG_MAX defines the maximum size (in bytes) of the log
 *   file. At application start the size of the log is checked and if it is too
 *   large, the file gets deleted. By default, APP_LOG_MAX is 1 MB.
 * </p>
 ***/
class Debug
{
    // MANIPULATORS

    /***
     * Initialize the debug module. This method should be called once at
     * application startup, in an assert. It checks if the log isn't too big,
     * and if it is the log is deleted. If APP_LOG_MAX is 0 (or smaller), the
     * log will never be deleted. As this method must be called from an 
     * assertion, it always returns true.
     * @returns bool
     * @static
     ***/
    function initialize()
    {
        if (file_exists(APP_LOG_FILE) && APP_LOG_MAX > 0 &&
            filesize(APP_LOG_FILE) > APP_LOG_MAX)
        {
            unlink(APP_LOG_FILE);
        }
        Debug::log(str_repeat('-', 60), DEBUG_LEVEL_SYSTEM);
        Debug::log(
            "Debug: start on " . date('r', DAWN_TIME_BEGIN),
            DEBUG_LEVEL_SYSTEM
        );
        return true;
    }

    /***
     * Log a message to the file APP_LOG_FILE, but only if APP_LOG is true. This
     * method always returns true, so that the assertion that calls this method
     * always succeeds.
     * @param $message the message to log
     * @param $severity the severity of the message
     * @returns bool
     * @static
     ***/
    function log($message, $severity = DEBUG_LEVEL_NOTICE)
    {
        if (APP_LOG && APP_LOG_LEVEL & $severity)
        {
            $state = Debug::getStateName();
            $level = Debug::getLogLevelName($severity);
            error_log("[ $state | $level ] $message\n", 3, APP_LOG_FILE);
        }
        return true;
    }

    /***
     * Set the state of the system. The new state of the system must be
     * logically next to the old state. If this is not the case, a message is
     * printed to the system log. To be able to call this method from within
     * assertions, this method always returns true.
     * @param state the new state of the system
     * @returns bool
     * @static
     ***/
    function setState($state)
    {
        if (isset($GLOBALS[DAWN_STATE]) &&
            $state != $GLOBALS[DAWN_STATE] << 1)
        {
            $name = Debug::getStateName($state);
            Debug::log(
                "Debug: setting system to illegal state: '$name'!",
                DEBUG_LEVEL_WARNING
            );
        }
        $GLOBALS[DAWN_STATE] = $state;
        $name = Debug::getStateName($state);
        Debug::log(
            "Debug: changing system state to $name",
            DEBUG_LEVEL_SYSTEM
        );
        return true;
    }

    /***
     * End the debug module. This method should be called once, at the end of
     * the application run inside an assertion. It sets the system state to the
     * final state and logs a shutdown message. This method always returns true
     * @bool
     * @static
     ***/
    function finish()
    {
        Debug::setState(DEBUG_STATE_END);
        Debug::log(
            'Debug: shutdown on ' . date('r') . "\n",
            DEBUG_LEVEL_SYSTEM
        );
        return true;
    }

    // ACCESSORS

    /***
     * Return the state of the system
     * @returns int
     * @private
     * @static
     ***/
    function getState()
    {
        return $GLOBALS[DAWN_STATE];
    }

    /***
     * Check the state of the system. If the system is in an incorrect state, a
     * message is printed to the log file
     * @param $caller the part of the system that wants the state checked
     * @param $state the state the system should be in
     * @static
     ***/
    function checkState($caller, $state)
    {
        if (!($state & Debug::getState()))
        {
            $name = Debug::getStateName();
            Debug::log(
                "$caller: system is in incorrect state: $name",
                DEBUG_LEVEL_WARNING
            );
        }
        return true;
    }

    /***
     * Get the name of the specified state. If the state is 0, the name of the
     * current system state is returned
     * @returns string
     * @private
     * @static
     ***/
    function getStateName($state = 0)
    {
        if ($state == 0)
        {
            $state = Debug::getState();
        }
        switch ($state)
        {
            case DEBUG_STATE_BEGIN : return 'BEGIN ';
            case DEBUG_STATE_CREATE: return 'CREATE';
            case DEBUG_STATE_BUILD : return 'BUILD ';
            case DEBUG_STATE_SHOW  : return 'SHOW  ';
            case DEBUG_STATE_END   : return 'END   ';
        }
        return 'INVALID';
    }

    function getLogLevelName($level)
    {
        switch($level)
        {
            case DEBUG_LEVEL_SYSTEM : return 'SYSTEM ';
            case DEBUG_LEVEL_NOTICE : return 'NOTICE ';
            case DEBUG_LEVEL_WARNING: return 'WARNING';
            case DEBUG_LEVEL_ERROR  : return 'ERROR  ';
        }
    }
}
?>
