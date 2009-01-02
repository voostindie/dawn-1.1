<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class DirectoryNotFoundException extends Exception
{
    var $directory;
    
    function DirectoryNotFoundException($directory)
    {
        $this->Exception();
        $this->directory = $directory;
    }
    
    function getName()
    {
        return 'Directory Not Found';
    }
    
    function getDescription()
    {
        return "The directory <code>$this->directory</code> could not be " .
            "opened. Please make sure it exists and has the right permissions";
    }
}
?>
