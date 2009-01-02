<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class InvalidPropertyException extends Exception
{
    var $type;
    var $window;
    var $component;
    var $property;

    function InvalidPropertyException($type, $window, $component, $property)
    {
        $this->Exception();
        $this->type      = $type;
        $this->window    = $window;
        $this->component = $component;
        $this->property  = $property;
    }

    function getName()
    {
        return 'Invalid Component Property';
    }

    function getDescription()
    {
        return "The $this->type property <b>$this->property</b> was " .
            "requested from the component <b>$this->component</b> of " .
            "window <b>$this->window</b>, but this property doesn't exist.";
    }
}
?>
