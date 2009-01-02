<?php
require_once(DAWN_SYSTEM. 'Exception.php');

/***
 * Class <code>ObjectInvalidException</code> implements the 
 * code>Exception</code> interface for an exception thrown by class
 * <code>Object</code>.
 * <p>
 *   Although technically this class is a part of the <code>Object</code> 
 *   component, it is defined in a separate file. The reason is that this class
 *   is only included when an exception actually has to be thrown, which is only
 *   true if the object is initialized for the first time. Thus, exceptions cannot
 *   be thrown when the object is restored from cache.
 * </p>
 * <p>
 *   Class <code>Object</code> throws this exception when, after initialization,
 *   there are still arguments with a value of <code>OBJECT_INVALID_VALUE</code>.
 * </p>
 ***/
class ObjectInvalidException extends Exception
{
    // DATA MEMBERS
    
    /***
     * The ID of the object that triggered the exception
     * @type string
     ***/
    var $id;
    
    /***
     * The name of the object's class
     * @type string
     ***/
    var $class;
    
    /***
     * The list of invalid arguments for the object
     * @type array
     ***/
    var $keys;
    
    // CREATORS
    
    /***
     * Create a new exception
     * @param $objectId the ID of the object that triggered the exception
     * @param $objectAlias the alias of said object
     * @param $invalidKeys the list of arguments with an invalid value
     ***/
    function ObjectInvalidException($objectId, $className, $invalidKeys)
    {
        $this->Exception();
        $this->id    = $objectId;
        $this->class = $className;
        $this->keys  = $invalidKeys;
    }
    
    /***
     * @returns string
     ***/
    function getName()
    {
        return 'Missing Object Argument(s)';
    }
    
    /***
     * @returns string
     ***/
    function getDescription()
    {
        return "The object of class <b>$this->class</b> with id <b>$this->id</b> " .
            "has a number of required arguments that aren't specified.<br>" .
            "These are: <b>" . join(', ', $this->keys) . "</b>.";
    }
}
?>
