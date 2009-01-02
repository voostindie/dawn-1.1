<?php
/***
 * Class <code>Error</code> implements fatal errors
 * <p>
 *   This class is meant to be subclassed. Subclasses need only implement the
 *   methods <code>getName</code> and <code>getDescription</code> to reuse its
 *   functionality.
 * </p>
 * <p>
 *   When the method <code>halt</code> is called, the error is shown in the
 *   nicest way possible, and the application is halted.
 * </p>
 * <p>
 *   Errors are meant to be used only in the creational phase of the
 *   application. For example, when a connection with the database could
 *   not be made or if a configuration file is parsed and an error was found, an
 *   error may be executed immediately. Errors are <b>not</b> to be used
 *   when the application is in the running phase!
 * </p>
 ***/
class Exception {

    // CREATORS

    /***
     * Construct a new Exception
     ***/
    function Exception() {
        assert('Debug::checkState("Exception", ' .
               'DEBUG_STATE_CREATE | DEBUG_STATE_BUILD)');
    }

    // MANIPULATORS

    /***
     * Throw the exception. This is done is as nice a way as possible. This
     * means that if a Window class with alias 'error' can be included, the
     * exception will be shown in an application-specific popup window.
     * @returns void
     ***/
    function halt() {
        assert('Debug::log(\'Error: \' . $this->getName(),' . 'DEBUG_LEVEL_ERROR)');
        echo "<html><head><title>Fatal Error!</title></head><body>\n";
        echo '<b>', $this->getName(), '</b>:<br>', $this->getDescription();
        echo "</body></html>\n";
        assert('Debug::log(\'The application terminated unexpectedly\',' . 'DEBUG_LEVEL_ERROR)');
        exit -1;
    }

    // ACCESSORS

    /***
     * Return the name of this error
     * @returns string
     ***/
    function getName() {
        return 'Unknown Error';
    }

    /***
     * Return the description of this error
     * @returns string
     ***/
    function getDescription() {
        return 'No description available for this error';
    }
}
?>
