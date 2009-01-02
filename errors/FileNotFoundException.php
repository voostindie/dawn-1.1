<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class FileNotFoundException extends Exception
{
    var $filename;
    
    function FileNotFoundException($filename)
    {
        $this->Exception();
        $this->filename = $filename;
    }
    
    function getName()
    {
        return 'File Not Found';
    }
    
    function getDescription()
    {
        return "The file <code>$this->filename</code> could not be found.";
    }
}
?>
