<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class ConnectionException extends Exception
{
    var $name;
    var $host;
    
    function ConnectionException($name, $host)
    {
        $this->Exception();
        $this->name = $name;
        $this->host = $host;
    }
    
    function getName()
    {
        return 'Database Connection Failed';
    }
    
    function getDescription()
    {
        return "A connection with the database <code>$this->name</code> on " .
            "host <code>$this->host</code> could not be established. Please " .
            "check the authentication settings and make sure the database " .
            "server is running";
    }
}
?>
