<?php
require_once(DAWN_SYSTEM . 'Exception.php');

class ComponentLoaderException extends Exception
{
    var $window;
    var $component;

    function ComponentLoaderException($window, $component)
    {
        $this->Exception();
        $this->window    = $window;
        $this->component = $component;
    }

    function getName()
    {
        return 'Error While Loading Components';
    }

    function getDescription()
    {
        return "While loading the components on window <b>$this->window</b> " .
            "the component <b>$this->component</b> was attempted to be " .
            "created from scratch, while it should be loaded from the cache." .
            "The most likely cause of this error is that the a static " .
            "property of this component is requested in the method " .
            "<b>load</b> of another component on the window.";
    }
}
?>
