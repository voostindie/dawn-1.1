<?php
require_once(DAWN_SYSTEM. 'Exception.php');

/***
 * Class <code>ObjectUnknownException</code> implements the 
 * <code>Exception</code> interface for an exception thrown by class
 * <code>Object</code>.
 * <p>
 *   Although technically this class is a part of the <code>Object</code> 
 *   component, it is defined in a separate file. The reason is that this class
 *   is only included when an exception actually has to be thrown, which is only
 *   true if the object is initialized for the first time. Thus, exceptions cannot
 *   be thrown when the object is restored from cache.
 * </p>
 * <p>
 *   Class <code>Object</code> throws this exception when, during initialization,
 *   an argument is specified the object doesn't know.
 * </p>
 ***/
class ObjectUnknownException extends Exception
{
    // DATA MEMBERS
    
    /***
     * The ID of the object that triggered the exception
     * @type string
     ***/
    var $id;
    
    /***
     * The object's class
     * @type string
     ***/
    var $class;
    
    /***
     * The list of valid arguments for the object
     * @type array
     ***/
    var $keys;
    
    /***
     * The unknown argument
     * @type string
     ***/
    var $argument;
    
    // CREATORS
    
    /***
     * Create a new exception
     * @param $objectId the ID of the object that triggered the exception
     * @param $objectAlias the alias of said object
     * @param $invalidKeys the list of arguments with an invalid value
     ***/
    function ObjectUnknownException($oid, $class, $validKeys, $unknownArgument)
    {
        $this->Exception();
        $this->id       = $oid;
        $this->class    = $class;
        $this->keys     = $validKeys;
        $this->argument = $unknownArgument;
    }
    
    /***
     * @returns string
     ***/
    function getName()
    {
        return 'Unknown Object Argument(s)';
    }
    
    /***
     * @returns string
     ***/
    function getDescription()
    {
        return "The object of class <b>$this->class</b> with id " .
            "<b>$this->id</b> received an argument it doesn't know: " .
            "<b>$this->argument</b>. The arguments this object does know " .
            "are: <b>" . join(', ', $this->keys) . "</b>.";
    }
}
?>
