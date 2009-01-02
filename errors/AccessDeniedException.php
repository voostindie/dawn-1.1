<?php
require_once(DAWN_SYSTEM . 'Exception.php');

/***
 * Class <code>ConfigException</code> is thrown when a required entry wasn't
 * found in a configuration file.
 ***/
class AccessDeniedException extends Exception
{
    // CREATORS
    
    /***
     * @returns string
     ***/
    function getName()
    {
        return 'Access Denied';
    }
    
    /***
     * @returns string
     ***/
    function getDescription()
    {
        return 'You are trying to access a non-existent page or a page ' .
            'you do not have the necessary privileges for. Please stop ' .
            'trying to hack this site!';
    }
}
