<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class DatabaseException extends Exception
{
    var $alias;
    var $valid;
    
    function DatabaseException($illegalAlias, $validAliases)
    {
        $this->Exception();
        $this->alias = $illegalAlias;
        $this->valid = $validAliases;
    }
    
    function getName()
    {
        return 'Illegal Database Alias';
    }
    
    function getDescription()
    {
        return "The specified database alias <code>$this->alias</code> is " .
            "invalid. Valid aliases are: " . join(', ', $this->valid) . ".";
    }
}
?>
