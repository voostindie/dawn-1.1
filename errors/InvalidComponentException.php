<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class InvalidComponentException extends Exception
{
    var $window;
    var $component;
    
    function InvalidComponentException($window, $component)
    {
        $this->Exception();
        $this->window    = $window;
        $this->component = $component;
    }
    
    function getName()
    {
        return 'Invalid Window Component';
    }
    
    function getDescription()
    {
        return "A property of the component <b>$this->component</b> on " .
               "window <b>$this->window</b> was requested, but the component " .
               "doesn't exist.";
    }
}
?>
