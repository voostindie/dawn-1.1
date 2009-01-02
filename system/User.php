<?php
require_once(DAWN_SYSTEM . 'Object.php');
require_once(DAWN_SYSTEM . 'History.php');

/***
 * Class <code>User</code> defines the base class for all kinds of users in the
 * framework.
 * <p>
 *   This class supplies a number of methods that can be overridden by
 *   subclasses to execute additional code:
 * </p>
 * <ul>
 *   <li>
 *     <code>login</code>: execute the login procedure
 *   </li>
 *   <li>
 *     <code>logout</code>: execute the logout procedure
 *   </li>
 *   <li>
 *     <code>setLocale</code>: change the user's locale
 *   </li>
 *   <li>
 *     <code>getId</code>: get the user's id
 *   </li>
 *   <li>
 *     <code>getLogin</code>: get the user's login name
 *   </li>
 *   <li>
 *     <code>getDescription</code>: get a description of the user's name
 *   </li>
 *   <li>
 *     <code>getLocale</code>: get the user's locale
 *   </li>
 *   <li>
 *     <code>hasAccess</code>: check if a user has access to some part of the
 *     system. The parameter passed to this function is configurable in the
 *     application-specific configuration files.
 *   </li>
 * </ul>
 * <p>
 *   Additionally, this class implements a login form, using the framework's own
 *   capabilities. Thus, if an application would override the system-wide
 *   login-window and/or -widgets, the form would change accordingly.
 * </p>
 * <p>
 *   <b>IMPORTANT</b>: the application stores the user as a session object on
 *   the server. At application shutdown, the user's settings are stored in a
 *   file on the server and when the same user restarts the application, these
 *   settings are loaded again. It is <b>not</b> possible to store object
 *   references inside the User object, because then PHP will try to store these
 *   objects as well, which will almost certainly lead to circular references
 *   (user -> application -> user -> ...) or, in less nice words, a crashing
 *   server. This is the reason that the <code>initialize</code> method is
 *   passed a reference to the Application object; it cannot be permanently
 *   stored inside the User object.
 * </p>
 ***/
class User extends Object
{
    // DATA MEMBERS

    /***
     * This user's locale
     * @type string
     ***/
    var $locale;

    /***
     * This user's history
     * @type array
     ***/
    var $history;

    // CREATORS

    /***
     * Construct a new <code>User</code> object.
     ***/
    function User()
    {
        $this->Object('user');
        $this->locale  = '';
        $this->history = new History;
    }

    function preCreate()
    {
        parent::preCreate();
        $this->setProperty('type'  , '');
    }

    function postCreate()
    {
        $this->deleteProperty('type');
        parent::postCreate();
    }

    // MANIPULATORS

    /***
     * Perform the login procedure. Overriding classes can use this method to
     * check the login and password against something, like a record in a file
     * or a database-table. If the login procedure was succesful, this method
     * should return <code>true</code>, and <code>false</code> otherwise.
     * @param $application the Application object
     * @param $login the username
     * @param $password the password
     * @returns bool
     ***/
    function login(&$application, $login, $password)
    {
    }

    /***
     * Perform the logout procedure. Overriding classes must implement this method to
     * render the User object invalid.
     * @param $application the Application object
     * @returns void
     ***/
    function logout(&$application)
    {
    }

    /***
     * Change the locale of this user
     * @param $application the Application object
     * @param $locale the name of the new locale
     * @returns void
     ***/
    function setLocale($locale)
    {
        $this->locale = $locale;
    }

    // ACCESSORS

    /***
     * Get the id for this user. This method must be overridden by subclasses.
     * @returns string
     ***/
    function getId()
    {
        return 0;
    }

    /***
    * Get the login name for this user. This method must be overridden by
    * subclasses.
    * @returns string
    ***/
    function getLogin()
    {
        return 'none';
    }

    /***
     * Get a description for this user
     * @returns string
     ***/
    function getDescription()
    {
        return 'Default user';
    }

    /***
     * Check whether this user has access to part of the application marked with
     * $access. By default this method always returns <code>true</code>,
     * subclasses can override it to implement their own (application-specific)
     * security mechanism.
     * @returns bool
     ***/
    function hasAccess($access)
    {
        return true;
    }

    /***
     * Return this user's locale. If no locale was set, the empty string is
     * returned
     * @returns string
     ***/
    function getLocale()
    {
        return $this->locale;
    }
    
    function &getHistory()
    {
        if (!isset($this->history))
        {
            $this->history = new History();
        }
        return $this->history;
    }

    /***
     * Check whether this user is valid (logged in). This method must be
     * overridden by subclasses, or else the user will never be valid
     * @returns bool
     ***/
    function isValid()
    {
        return false;
    }
}
?>
