<?php
require_once(DAWN_SYSTEM . 'Exception.php');

/***
 * Class <code>ConfigException</code> is thrown when a required entry wasn't
 * found in a configuration file.
 ***/
class ConfigException extends Exception
{
    // DATA MEMBERS
    
    /***
     * The required entry that doesn't exist
     * @type string
     ***/
    var $entry;
    
    /***
     * The entity that is missing a section, e.g. 'window'
     * @type string
     ***/
    var $entity;
     
    /***
     * The id of the identity
     * @type string
     ***/
    var $id;
    
    // CREATORS
    
    /***
     * Construct a new <code>ConfigException</code>
     * @param $entry the name of the required but missing entry
     ***/
    function ConfigException($entry, $entity = '', $id = '')
    {
        $this->Exception();
        $this->entry  = $entry;
        $this->entity = $entity;
        $this->id     = $id;
    }
    
    /***
     * @returns string
     ***/
    function getName()
    {
        return 'Entry Missing In Configuration';
    }
    
    /***
     * @returns string
     ***/
    function getDescription()
    {
        return "The entry <b>$this->entry</b> " . 
            (($this->entity != '' && $this->id != '')
                ? "for the $this->entity <b>$this->id</b> " 
                : "") .
            "could not be found in the configuration.";
    }
}
