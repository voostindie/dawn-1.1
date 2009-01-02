<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class WindowException extends Exception
{
    var $id;
    var $section;
    
    function WindowException($id, $section)
    {
        $this->Exception();
        $this->id = $id;
        $this->section = $section;
    }
    
    function getName()
    {
        return 'Missing Window Section';
    }
    
    function getDescription()
    {
        return "The window with id <b>$this->id</b> could not be created " .
            "because the required section <b>$this->section</b> doesn't exist.";
    }
}
?>
