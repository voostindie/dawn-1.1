<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class CyclicDependencyException extends Exception
{
    var $window;
    var $caller;
    var $target;
    
    function CyclicDependencyException($window, $caller, $target)
    {
        $this->Exception();
        $this->window = $window;
        $this->caller = $caller;
        $this->target = $target;
    }
    
    function getName()
    {
        return 'Cyclic Component Dependency';
    }
    
    function getDescription()
    {
        return "The window with id <b>$this->window</b> contains two " .
                "components <b>$this->caller</b> and <b>$this->target</b> " .
                "that depend on each other. Because of this cyclic dependency, " .
                "the components cannot be built in the correct order.";
    }
}
?>
