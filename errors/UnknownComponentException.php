<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class UnknownComponentException extends Exception
{
    var $window;
    var $component;
    
    function UnknownComponentException($window, $component)
    {
        $this->Exception();
        $this->window    = $window;
        $this->component = $component;
    }
    
    function getName()
    {
        return 'Unkown Component';
    }
    
    function getDescription()
    {
        return "The layout of window <b>$this->window</b> refers to the " .
               "component <b>$this->component</b>. However, this component " .
                "doesn't exist on the window.";
    }
}
?>
