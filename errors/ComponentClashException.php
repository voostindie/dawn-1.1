<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class ComponentClashException extends Exception
{
    var $window;
    var $component;
    
    function ComponentClashException($window, $component)
    {
        $this->Exception();
        $this->window    = $window;
        $this->component = $component;
    }
    
    function getName()
    {
        return 'Clash In Componentnames';
    }
    
    function getDescription()
    {
        return "A component with the name <b>$this->component</b> was added " .
               "to the window <b>$this->window</b>. However, a component " .
               "with this name already exists in the window.";
    }
}
?>
